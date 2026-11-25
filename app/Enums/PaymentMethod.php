<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Payment methods from plan 1.2. JazzCash, Easypaisa and card only appear on
 * the Donate page once their merchant accounts are approved and switched on in
 * config/payments.php (PAYMENTS_*_ENABLED in .env).
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

    /** Online methods confirm instantly through the provider; bank transfers need Finance. */
    public function isOnline(): bool
    {
        return $this !== self::BankTransfer;
    }

    public function isAvailable(): bool
    {
        return (bool) config("payments.methods.{$this->value}.enabled");
    }

    /** @return list<self> */
    public static function available(): array
    {
        return array_values(array_filter(self::cases(), fn (self $m) => $m->isAvailable()));
    }
}
