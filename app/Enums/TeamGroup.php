<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** Sections of the Leadership page (plan 1.1). */
enum TeamGroup: string implements HasLabel
{
    case Patron = 'patron';
    case Executive = 'executive';
    case Adviser = 'adviser';

    public function getLabel(): string
    {
        return match ($this) {
            self::Patron => 'Patrons',
            self::Executive => 'Executive Body',
            self::Adviser => 'Advisers',
        };
    }
}
