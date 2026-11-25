<?php

namespace App\Livewire;

use App\Enums\PaymentMethod;
use App\Models\Cause;
use App\Models\Donation;
use App\Models\GivingType;
use App\Payments\GatewayManager;
use App\Services\DonationService;
use App\Settings\DonationSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL as UrlGenerator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * The Donate page (plan 1.1): 1 Type of giving · 2 Cause · 3 Amount · 4 Payment.
 * One column on phones, the amount always visible, no account needed.
 */
class DonateForm extends Component
{
    use WithFileUploads;

    public int $step = 1;

    /** Step 1 shows the most used types first; the rest sit behind "Show more types". */
    public const PRIMARY_TYPES = 4;

    public bool $showAllTypes = false;

    #[Url(as: 'type', except: '')]
    public string $typeSlug = '';

    #[Url(as: 'cause', except: '')]
    public string $causeSlug = '';

    /** A preset value as a string, or 'other'. */
    public string $amountChoice = '';

    public ?string $customAmount = null;

    public ?int $fitranaPeople = null;

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public bool $anonymous = false;

    public string $method = '';

    /** @var TemporaryUploadedFile|null */
    public $receipt = null;

    public function mount(): void
    {
        // ?cause= may be a cause slug or a program slug (from "Donate to this program").
        if ($this->causeSlug !== '' && ! $this->cause) {
            $byProgram = Cause::active()->whereHas('program', fn ($q) => $q->where('slug', $this->causeSlug))->first();
            $this->causeSlug = $byProgram?->slug ?? '';
        }

        if ($this->typeSlug !== '' && ! $this->type) {
            $this->typeSlug = '';
        }

        $amount = (int) request()->query('amount');
        if ($amount > 0) {
            $this->setAmount($amount);
        }

        if (count($methods = PaymentMethod::available()) === 1) {
            $this->method = $methods[0]->value;
        }

        // A preselected type that is not among the first few keeps the full list open.
        if ($this->type && $this->types->search(fn ($t) => $t->is($this->type)) >= self::PRIMARY_TYPES) {
            $this->showAllTypes = true;
        }

        // Open at the first step still to answer, e.g. "Pay this as Zakat" lands on step 3.
        if ($this->type) {
            $this->step = ($this->causeSlug !== '' || $this->amountChoice !== '') ? 3 : 2;
        }
    }

    #[Computed]
    public function types(): Collection
    {
        return GivingType::active()->get();
    }

    #[Computed]
    public function causes(): Collection
    {
        return Cause::active()->with('eligibleGivingTypes:id')->get();
    }

    #[Computed]
    public function type(): ?GivingType
    {
        return $this->types->firstWhere('slug', $this->typeSlug);
    }

    #[Computed]
    public function cause(): ?Cause
    {
        return $this->causes->firstWhere('slug', $this->causeSlug);
    }

    #[Computed]
    public function amount(): ?int
    {
        if ($this->type?->slug === 'fitrana' && $this->fitranaPeople && $this->settings->fitrana_amount) {
            return $this->fitranaPeople * $this->settings->fitrana_amount;
        }

        $value = $this->amountChoice === 'other' ? $this->customAmount : $this->amountChoice;
        $value = (int) preg_replace('/\D/', '', (string) $value);

        return $value > 0 ? $value : null;
    }

    #[Computed]
    public function settings(): DonationSettings
    {
        return app(DonationSettings::class);
    }

    /** A restricted fund chosen for a cause Finance hasn't marked eligible. */
    #[Computed]
    public function causeNeedsReview(): bool
    {
        return $this->type?->is_restricted && $this->cause
            && ! $this->cause->eligibleGivingTypes->contains('id', $this->type->id);
    }

    public function chooseType(string $slug): void
    {
        $this->typeSlug = $slug;
        $this->resetErrorBag('typeSlug');
        $this->step = max($this->step, 2);
    }

    public function chooseCause(string $slug): void
    {
        $this->causeSlug = $this->causeSlug === $slug ? '' : $slug;
    }

    public function setAmount(int $amount): void
    {
        $presets = config('payments.amounts.presets');
        if (in_array($amount, $presets, true)) {
            $this->amountChoice = (string) $amount;
            $this->customAmount = null;
        } else {
            $this->amountChoice = 'other';
            $this->customAmount = (string) $amount;
        }
    }

    public function goTo(int $step): void
    {
        // Earlier steps can be revisited freely; moving ahead needs the steps before it done.
        for ($s = 1; $s < $step; $s++) {
            $this->validateStep($s);
        }
        $this->step = max(1, min(4, $step));
    }

    public function next(): void
    {
        $this->validateStep($this->step);
        $this->step = min(4, $this->step + 1);
    }

    public function back(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    protected function validateStep(int $step): void
    {
        $min = config('payments.amounts.minimum');
        $max = config('payments.amounts.maximum');

        match ($step) {
            1 => $this->validate(
                ['typeSlug' => ['required', Rule::in($this->types->pluck('slug'))]],
                ['typeSlug.required' => 'Choose a type of giving to continue.'],
            ),
            2 => $this->validate(['causeSlug' => ['nullable', Rule::in($this->causes->pluck('slug'))]]),
            3 => $this->validateAmountAndDonor($min, $max),
            default => null,
        };
    }

    /** Shows every problem on this step at once: amount and donor details together. */
    protected function validateAmountAndDonor(int $min, int $max): void
    {
        $errors = [];

        try {
            $this->validateDonor();
        } catch (ValidationException $e) {
            $errors = $e->errors();
        }

        $amount = $this->amount;
        if (! $amount || $amount < $min || $amount > $max) {
            $errors['amount'] = ['Choose an amount between Rs '.number_format($min).' and Rs '.number_format($max).'.'];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    protected function validateDonor(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:/^\+?[0-9][0-9 \-]{8,17}$/'],
            'email' => ['nullable', 'email:rfc', 'max:190'],
        ], [
            'name.required' => 'Enter your full name.',
            'phone.required' => 'Enter your mobile number so we can reach you about your donation.',
            'phone.regex' => 'Enter a mobile number like 0300 1234567 or +92 300 1234567.',
            'email.email' => 'Enter a valid email address, or leave it empty.',
        ]);
    }

    public function donate(DonationService $service, GatewayManager $gateways)
    {
        foreach ([1, 2, 3] as $s) {
            $this->validateStep($s);
        }

        $available = array_map(fn ($m) => $m->value, PaymentMethod::available());
        $this->validate([
            'method' => ['required', Rule::in($available)],
            'receipt' => [Rule::requiredIf($this->method === PaymentMethod::BankTransfer->value), 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'method.required' => 'Choose how you would like to pay.',
            'receipt.required' => 'Upload a screenshot or photo of your transfer receipt.',
            'receipt.mimes' => 'Upload the receipt as a photo (JPG, PNG) or a PDF.',
            'receipt.max' => 'The receipt must be smaller than 5 MB.',
        ]);

        $key = 'donate:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 6)) {
            $this->addError('method', 'Too many attempts. Please wait a few minutes and try again, or message us on WhatsApp.');

            return null;
        }
        RateLimiter::hit($key, 600);

        $method = PaymentMethod::from($this->method);

        $donation = $service->start([
            'giving_type_id' => $this->type->id,
            'cause_id' => $this->cause?->id,
            'amount' => $this->amount,
            'donor_name' => trim($this->name),
            'donor_phone' => trim($this->phone),
            'donor_email' => trim($this->email) ?: null,
            'is_anonymous' => $this->anonymous,
            'payment_method' => $method,
            'ip_address' => request()->ip(),
        ], $method === PaymentMethod::BankTransfer ? $this->receipt : null);

        if ($method->isOnline()) {
            return redirect()->away($gateways->for($method)->checkoutUrl($donation));
        }

        return redirect()->to(static::thankYouUrl($donation));
    }

    public static function thankYouUrl(Donation $donation): string
    {
        return UrlGenerator::signedRoute('donate.thank-you', $donation->reference, now()->addDays(7));
    }

    public function render(): View
    {
        return view('livewire.donate-form', [
            'methods' => PaymentMethod::available(),
            'presets' => config('payments.amounts.presets'),
        ]);
    }
}
