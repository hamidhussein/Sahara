<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The site is English; any post, project, program or page can be marked Urdu,
 * which makes only its content area right-to-left (plan 0.4, 1.2).
 */
enum ContentLanguage: string implements HasLabel
{
    case English = 'en';
    case Urdu = 'ur';

    public function getLabel(): string
    {
        return match ($this) {
            self::English => 'English',
            self::Urdu => 'اردو (Urdu)',
        };
    }

    public function isRtl(): bool
    {
        return $this === self::Urdu;
    }

    /** Attributes for the content wrapper: lang and dir. */
    public function attributes(): string
    {
        return $this === self::Urdu ? 'lang="ur" dir="rtl"' : 'lang="en"';
    }
}
