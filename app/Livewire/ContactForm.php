<?php

namespace App\Livewire;

use App\Mail\ContactAutoReply;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Settings\SiteSettings;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Contact page form (plan 1.1): Name · Email or phone · Subject · Message.
 * Saved for staff in Admin → Messages, emailed to the office, and
 * acknowledged to the sender when they give an email address.
 */
class ContactForm extends Component
{
    public string $name = '';

    public string $contact = '';

    public string $subject = '';

    public string $message = '';

    /** Honeypot: hidden from people, filled in by bots. */
    public string $website = '';

    public bool $sent = false;

    /** Links such as "Start a partnership" open the form with the subject filled in. */
    public function mount(): void
    {
        $subject = request()->query('subject');
        $this->subject = is_string($subject) ? str($subject)->squish()->limit(160, '')->value() : '';
    }

    public function send(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'contact' => ['required', 'string', 'max:190'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'name.required' => 'Enter your name.',
            'contact.required' => 'Enter an email address or phone number so we can reply.',
            'subject.required' => 'Tell us what your message is about.',
            'message.required' => 'Write your message.',
            'message.min' => 'Your message is a little short. Add a few more words so we can help.',
        ]);

        [$email, $phone] = $this->splitContact($this->contact);

        if (! $email && ! $phone) {
            throw ValidationException::withMessages([
                'contact' => 'Enter a valid email address (name@example.com) or a phone number (0300 1234567).',
            ]);
        }

        // Bots fill the hidden field: pretend it worked and keep nothing.
        if ($this->website !== '') {
            $this->sent = true;

            return;
        }

        $key = 'contact:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'message' => 'You have sent several messages in a short time. Please wait a few minutes, or call us.',
            ]);
        }
        RateLimiter::hit($key, 900);

        $contactMessage = ContactMessage::create([
            'name' => trim($this->name),
            'email' => $email,
            'phone' => $phone,
            'subject' => trim($this->subject),
            'message' => trim($this->message),
            'ip_address' => request()->ip(),
        ]);

        Mail::to(app(SiteSettings::class)->email)->queue(new ContactMessageReceived($contactMessage));

        if ($email) {
            Mail::to($email)->queue(new ContactAutoReply($contactMessage));
        }

        $this->reset('name', 'contact', 'subject', 'message');
        $this->sent = true;
    }

    /** @return array{0: ?string, 1: ?string} [email, phone] */
    protected function splitContact(string $value): array
    {
        $value = trim($value);

        if (Str::contains($value, '@')) {
            return [filter_var($value, FILTER_VALIDATE_EMAIL) ? Str::lower($value) : null, null];
        }

        return [null, preg_match('/^\+?[0-9][0-9 \-]{8,17}$/', $value) ? $value : null];
    }

    public function render(): View
    {
        return view('livewire.contact-form');
    }
}
