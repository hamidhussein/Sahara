<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProjectStatus: string implements HasColor, HasLabel
{
    case Planned = 'planned';
    case Ongoing = 'ongoing';
    case Completed = 'completed';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Planned => 'gray',
            self::Ongoing => 'info',
            self::Completed => 'success',
        };
    }

    /** Tailwind classes for the public status tag. Orange stays reserved for Donate. */
    public function tagClasses(): string
    {
        return match ($this) {
            self::Planned => 'bg-line text-ink',
            self::Ongoing => 'bg-water text-white',
            self::Completed => 'bg-primary text-white',
        };
    }
}
