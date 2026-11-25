<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** News filters from plan 1.1 (News). */
enum PostCategory: string implements HasLabel
{
    case Water = 'water';
    case Health = 'health';
    case Education = 'education';
    case SocialServices = 'social-services';
    case Qurbani = 'qurbani';
    case YouthCommunity = 'youth-community';
    case Announcements = 'announcements';

    public function getLabel(): string
    {
        return match ($this) {
            self::Water => 'Water',
            self::Health => 'Health',
            self::Education => 'Education',
            self::SocialServices => 'Social Services',
            self::Qurbani => 'Qurbani',
            self::YouthCommunity => 'Youth & Community',
            self::Announcements => 'Announcements',
        };
    }
}
