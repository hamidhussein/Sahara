<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Plan 2.2: new → reviewing → approved → done. */
enum HelpStatus: string implements HasColor, HasLabel
{
    case New = 'new';
    case Reviewing = 'reviewing';
    case Approved = 'approved';
    case Done = 'done';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Reviewing => 'Reviewing',
            self::Approved => 'Approved',
            self::Done => 'Done',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'danger',
            self::Reviewing => 'warning',
            self::Approved => 'info',
            self::Done => 'success',
        };
    }
}
