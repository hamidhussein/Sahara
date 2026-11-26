<?php

use Spatie\Backup\Notifications\Notifiable;
use Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification;
use Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification;
use Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification;
use Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification;
use Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes;

/*
 * Backups (plan 1.4): the database and uploaded files, every night, kept 30
 * days, with an email if a backup fails or goes missing.
 *
 * - The code itself lives in Git, so it is not backed up here.
 * - Destinations: BACKUP_DISKS (comma separated). "backups" is a folder on the
 *   server; add an off-site disk (e.g. "s3" or a Google Drive disk) before
 *   launch, because a backup on the same server does not survive losing it.
 * - Set BACKUP_ARCHIVE_PASSWORD: archives contain donors' payment receipts.
 * - Restores are checked with `php artisan sswf:check-backup`.
 */

$disks = array_values(array_filter(array_map('trim', explode(',', (string) env('BACKUP_DISKS', 'backups')))));

return [

    'backup' => [
        'name' => 'sswf-website',

        'source' => [
            'files' => [
                // Uploaded photos, documents and QR codes (public) and payment receipts (private).
                'include' => [
                    storage_path('app/public'),
                    storage_path('app/private'),
                ],

                'exclude' => [
                    storage_path('app/private/livewire-tmp'),
                    storage_path('app/backup-temp'),
                    storage_path('app/backups'),
                ],

                'follow_links' => false,
                'ignore_unreadable_directories' => false,

                // Paths inside the zip start at storage/app, e.g. "public/12/photo.jpg".
                'relative_path' => storage_path('app'),
            ],

            'databases' => [
                env('DB_CONNECTION', 'mysql'),
            ],
        ],

        'database_dump_compressor' => null,
        'database_dump_file_timestamp_format' => null,
        'database_dump_filename_base' => 'database',
        'database_dump_file_extension' => '',

        'destination' => [
            'compression_method' => ZipArchive::CM_DEFAULT,
            'compression_level' => 9,
            'filename_prefix' => 'sswf-',
            'disks' => $disks,
            // Keep going to the next destination if one fails (e.g. off-site is down).
            'continue_on_failure' => true,
        ],

        'temporary_directory' => storage_path('app/backup-temp'),

        'password' => env('BACKUP_ARCHIVE_PASSWORD'),
        'encryption' => 'default',
        'verify_backup' => true,
        'tries' => 2,
        'retry_delay' => 60,
    ],

    /*
     * Email only when something needs attention; successful runs stay quiet.
     */
    'notifications' => [
        'notifications' => [
            BackupHasFailedNotification::class => ['mail'],
            UnhealthyBackupWasFoundNotification::class => ['mail'],
            CleanupHasFailedNotification::class => ['mail'],
            BackupWasSuccessfulNotification::class => [],
            HealthyBackupWasFoundNotification::class => [],
            CleanupWasSuccessfulNotification::class => [],
        ],

        'notifiable' => Notifiable::class,

        'mail' => [
            'to' => env('BACKUP_NOTIFY_EMAIL', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'name' => env('MAIL_FROM_NAME', 'SSWF website'),
            ],
        ],

        'slack' => ['webhook_url' => '', 'channel' => null, 'username' => null, 'icon' => null],
        'discord' => ['webhook_url' => '', 'username' => '', 'avatar_url' => ''],
        'webhook' => ['url' => ''],
    ],

    'log_channel' => null,

    /*
     * `backup:monitor` warns when the newest backup is older than a day or
     * the backups grow past 5 GB.
     */
    'monitor_backups' => [
        [
            'name' => 'sswf-website',
            'disks' => $disks,
            'health_checks' => [
                MaximumAgeInDays::class => 1,
                MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

    /*
     * Every backup for 30 days (plan 1.4), then one a week for 8 weeks,
     * one a month for a year and one a year for 2 years.
     */
    'cleanup' => [
        'strategy' => DefaultStrategy::class,

        'default_strategy' => [
            'keep_all_backups_for_days' => 30,
            'keep_daily_backups_for_days' => 0,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 12,
            'keep_yearly_backups_for_years' => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],

        'tries' => 1,
        'retry_delay' => 0,
    ],

];
