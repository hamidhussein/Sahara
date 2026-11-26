<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Defaults follow plan 1.4: nightly backups kept 30 days, weekly restore check.
        $this->migrator->add('backups.schedule_enabled', true);
        $this->migrator->add('backups.frequency', 'daily');
        $this->migrator->add('backups.weekly_day', 0);
        $this->migrator->add('backups.time', '01:30');
        $this->migrator->add('backups.keep_days', 30);
        $this->migrator->add('backups.restore_check_enabled', true);
        $this->migrator->add('backups.notify_email', null);

        $this->migrator->add('backups.last_check_at', null);
        $this->migrator->add('backups.last_check_passed', null);
        $this->migrator->add('backups.last_check_summary', null);
        $this->migrator->add('backups.last_manual_at', null);
        $this->migrator->add('backups.last_manual_result', null);
    }
};
