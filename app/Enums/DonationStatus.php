<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DonationStatus: string implements HasColor, HasLabel
{
    /** Started; waiting for the payment provider's signed confirmation. */
    case Pending = 'pending';

    /** Bank transfer receipt uploaded; Finance has to check it. */
    case AwaitingConfirmation = 'awaiting_confirmation';

    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::AwaitingConfirmation => 'Receipt to check',
            self::Paid => 'Paid',
            self::Failed => 'Failed',
            self::Refunded => 'Refunded',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::AwaitingConfirmation => 'warning',
            self::Paid => 'success',
            self::Failed => 'danger',
            self::Refunded => 'info',
        };
    }
}
