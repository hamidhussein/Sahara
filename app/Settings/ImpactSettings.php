<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Manual impact numbers for the home page counters (plan 1.2: calculated
 * figures come later from projects and donations; these fill the gaps).
 */
class ImpactSettings extends Settings
{
    public int $founded_year;

    public int $water_plants;

    public int $report_year;

    public int $patients_examined;

    public int $eye_operations;

    public int $ramadan_families;

    public static function group(): string
    {
        return 'impact';
    }

    public function yearsServing(): int
    {
        return (int) now()->year - $this->founded_year;
    }
}
