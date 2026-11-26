<?php

namespace App\Enums;

use App\Payments\PaymentDetails;
use Filament\Support\Contracts\HasLabel;

/**
 * Payment methods from plan 1.2, each in "manual" or "gateway" mode
 * (config/payments.php):
 *  - manual: donor sends money to SSWF's bank, JazzCash or Easypaisa account
 *    and uploads the receipt; shown once the account details are in Settings.
 *  - gateway: provider checkout with a signed callback; shown once switched on.
 */
enum PaymentMethod: string implements HasLabel
{
    case JazzCash = 'jazzcash';
    case Easypaisa = 'easypaisa';
    case Card = 'card';
    case BankTransfer = 'bank_transfer';

    public function getLabel(): string
    {
        return match ($this) {
            self::JazzCash => 'JazzCash',
            self::Easypaisa => 'Easypaisa',
            self::Card => 'Debit/Credit Card',
            self::BankTransfer => 'Bank Transfer / Raast',
        };
    }

    /** Gateway mode: confirmed by the provider. Otherwise the donor uploads a receipt for Finance. */
    public function isOnline(): bool
    {
        return config("payments.methods.{$this->value}.mode", 'gateway') === 'gateway';
    }

    public function needsReceipt(): bool
    {
        return ! $this->isOnline();
    }

    public function isAvailable(): bool
    {
        if (! config("payments.methods.{$this->value}.enabled")) {
            return false;
        }

        // Manual methods need real account details (samples count only on non-live copies).
        return $this->isOnline() || PaymentDetails::for($this) !== null;
    }

    /** @return list<self> */
    public static function available(): array
    {
        return array_values(array_filter(self::cases(), fn (self $m) => $m->isAvailable()));
    }
}
