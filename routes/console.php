<?php

use App\Settings\BackupSettings;
use Illuminate\Support\Facades\Schedule;

/*
 * Scheduled tasks. On the server, Laravel's scheduler must run every minute
 * (Forge: "Scheduler" switched on, or cron: * * * * * php artisan schedule:run).
 *
 * Backups follow Admin → Settings → Backups: on or off, daily or weekly, at
 * the chosen time (Pakistan time). Before the database exists (first install)
 * the settings cannot be read, so nothing is scheduled.
 */

// Settings load lazily, so read them inside rescue(): before migrations have
// created them (first install, or this very migration) nothing is scheduled.
$backup = rescue(function () {
    $settings = app(BackupSettings::class);
    $settings->schedule_enabled; // forces loading; throws if the settings do not exist yet

    return $settings;
}, null, report: false);

if ($backup?->schedule_enabled) {
    [$hour, $minute] = array_map('intval', explode(':', $backup->time.':0'));
    $at = sprintf('%02d:%02d', min(23, $hour), min(59, $minute));

    $run = Schedule::command('sswf:backup')->timezone('Asia/Karachi')->onOneServer()->withoutOverlapping();
    $backup->frequency === 'weekly' ? $run->weeklyOn($backup->weekly_day, $at) : $run->dailyAt($at);

    // Warn by email if no fresh backup exists (age limit follows daily/weekly).
    Schedule::command('sswf:backup --monitor')->dailyAt('07:00')->timezone('Asia/Karachi')->onOneServer();
}

// Activity log entries are kept for a year (config/activitylog.php), then removed.
Schedule::command('activitylog:clean --force')->dailyAt('04:00')->timezone('Asia/Karachi')->onOneServer();

if ($backup?->restore_check_enabled) {
    // Every Sunday, prove the newest backup can actually be restored.
    Schedule::command('sswf:check-backup')->weeklyOn(0, '03:00')->timezone('Asia/Karachi')->onOneServer()
        ->emailOutputOnFailure($backup->notify_email ?: config('backup.notifications.mail.to'));
}
