<?php

namespace App\Payments;

use App\Enums\PaymentMethod;
use App\Settings\DonationSettings;
use Illuminate\Support\Facades\Storage;

/**
 * What a donor needs to pay by hand with a method: who to pay, the account or
 * mobile number, and the provider-issued QR code. Built from Admin → Settings
 * → Bank & Fitrana, with sample values dropped on the live site.
 */
final readonly class PaymentDetails
{
    /**
     * @param  array<string, string>  $rows  label => value, e.g. ['Account number' => '2010-…']
     */
    public function __construct(
        public PaymentMethod $method,
        public array $rows,
        public ?string $qrUrl,
        public bool $isSample,
        public ?string $copyValue,
    ) {}

    public static function for(PaymentMethod $method, ?DonationSettings $settings = null): ?self
    {
        $s = $settings ?? app(DonationSettings::class);

        [$rows, $qr, $copy] = match ($method) {
            PaymentMethod::BankTransfer => [[
                'Account title' => $s->account_title,
                'Bank' => $s->bank_name,
                'Account number' => $s->account_number,
                'IBAN' => $s->iban,
                'Raast ID' => $s->raast_id,
            ], $s->bank_qr, $s->iban ?: $s->account_number],
            PaymentMethod::JazzCash => [[
                'Account title' => $s->jazzcash_title,
                'JazzCash number' => $s->jazzcash_number,
            ], $s->jazzcash_qr, $s->jazzcash_number],
            PaymentMethod::Easypaisa => [[
                'Account title' => $s->easypaisa_title,
                'Easypaisa number' => $s->easypaisa_number,
            ], $s->easypaisa_qr, $s->easypaisa_number],
            PaymentMethod::Card => [[], null, null],
        };

        $isSample = collect($rows)->contains(fn ($v) => SampleDetails::isSample($v)) || SampleDetails::isSample($qr);
        $rows = array_filter($rows, fn ($v) => SampleDetails::usable($v));

        // Bank transfer needs an account number; wallets need their mobile account number.
        $required = match ($method) {
            PaymentMethod::BankTransfer => 'Account number',
            PaymentMethod::JazzCash => 'JazzCash number',
            PaymentMethod::Easypaisa => 'Easypaisa number',
            PaymentMethod::Card => null,
        };

        if (! $required || ! isset($rows[$required])) {
            return null;
        }

        return new self(
            method: $method,
            rows: $rows,
            qrUrl: SampleDetails::usable($qr) ? Storage::disk('public')->url($qr) : null,
            isSample: $isSample && SampleDetails::allowed(),
            copyValue: SampleDetails::usable($copy) ? $copy : null,
        );
    }

    /** Short how-to for the donor, in the order they do it. */
    public function steps(int $amount): array
    {
        $rs = 'Rs '.number_format($amount);

        return match ($this->method) {
            PaymentMethod::JazzCash => [
                'Open the JazzCash app (or dial *786#) and choose Send Money → Mobile Account, or scan the QR code.',
                "Send {$rs} to the number above and check the account title before you confirm.",
                'Take a screenshot of the confirmation and upload it below.',
            ],
            PaymentMethod::Easypaisa => [
                'Open the Easypaisa app (or dial *786#) and choose Send Money → Easypaisa Account, or scan the QR code.',
                "Send {$rs} to the number above and check the account title before you confirm.",
                'Take a screenshot of the confirmation and upload it below.',
            ],
            default => [
                'Open your banking app and transfer by IBAN, account number or Raast, or scan the QR code.',
                "Send {$rs} and check the account title before you confirm.",
                'Take a screenshot or photo of the receipt and upload it below.',
            ],
        };
    }
}
