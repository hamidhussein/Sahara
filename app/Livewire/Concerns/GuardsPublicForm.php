<?php

namespace App\Livewire\Concerns;

use App\Support\PhoneNumber;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Spam protection and phone handling shared by the Get Help and Get Involved
 * forms: a hidden honeypot field, a per-visitor limit, and one phone format.
 */
trait GuardsPublicForm
{
    /** Honeypot: hidden from people, filled in by bots. */
    public string $website = '';

    /** True when a bot filled the honeypot: pretend it worked and keep nothing. */
    protected function isBot(): bool
    {
        return $this->website !== '';
    }

    protected function limitSubmissions(string $form, string $field, int $max = 5): void
    {
        $key = $form.':'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, $max)) {
            throw ValidationException::withMessages([
                $field => 'You have sent this form several times in a short time. Please wait a few minutes, or call us.',
            ]);
        }

        RateLimiter::hit($key, 900);
    }

    /** "+923001234567", or a validation error on $field. */
    protected function phoneOrFail(string $value, string $field = 'phone'): string
    {
        $digits = PhoneNumber::international($value);

        if (! $digits) {
            throw ValidationException::withMessages([$field => 'Enter a mobile number we can call, like 0300 1234567.']);
        }

        return '+'.$digits;
    }
}
