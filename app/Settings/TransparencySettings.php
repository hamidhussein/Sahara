<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/** Lists on the Transparency page that Finance keeps up to date (Admin → Transparency). */
class TransparencySettings extends Settings
{
    /** @var list<string> "How we are funded" */
    public array $funding;

    /** @var list<string> "What we own" */
    public array $assets;

    /** @var list<string> "Our Zakat and Khums policy" */
    public array $policy;

    public static function group(): string
    {
        return 'transparency';
    }
}
