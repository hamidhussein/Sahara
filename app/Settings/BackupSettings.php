<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Backup schedule and retention, edited in Admin → Settings → Backups.
 * Destinations and the archive password stay in .env (server secrets).
 */
class BackupSettings extends Settings
{
    public bool $schedule_enabled;

    /** 'daily' or 'weekly' */
    public string $frequency;

    /** 0 = Sunday … 6 = Saturday, used when weekly */
    public int $weekly_day;

    /** "HH:MM", Pakistan time */
    public string $time;

    /** Keep every backup this many days (then weekly/monthly/yearly thinning). */
    public int $keep_days;

    public bool $restore_check_enabled;

    public ?string $notify_email;

    // Result of the last restore check, shown on the Backups page.
    public ?string $last_check_at;

    public ?bool $last_check_passed;

    public ?string $last_check_summary;

    // Result of the last backup started from the Backups page.
    public ?string $last_manual_at;

    public ?string $last_manual_result;

    public static function group(): string
    {
        return 'backups';
    }

    /** The scheduler expression for automatic backups, in Pakistan time. */
    public function cronDescription(): string
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return $this->frequency === 'weekly'
            ? "Every {$days[$this->weekly_day]} at {$this->time}"
            : "Every day at {$this->time}";
    }
}
