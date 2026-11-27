<?php

use App\Backups\BackupManager;
use App\Console\Commands\CheckBackup;
use App\Enums\Role;
use App\Filament\Pages\ManageBackupSettings;
use App\Jobs\RunBackup;
use App\Jobs\RunRestoreCheck;
use App\Models\User;
use App\Settings\BackupSettings;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;

/*
 * The backup itself and the restore check need MySQL and the mysqldump tool,
 * so they are run for real with `php artisan backup:run` and
 * `php artisan sswf:check-backup` (see README). These tests guard the setup.
 */

it('backs up the database and uploaded files, including private receipts', function () {
    expect(config('backup.backup.source.files.include'))
        ->toContain(storage_path('app/public'))
        ->toContain(storage_path('app/private'))
        ->and(config('backup.backup.source.databases'))->toBe([config('database.default')])
        ->and(config('backup.backup.source.files.exclude'))->toContain(storage_path('app/backups'));
});

it('keeps every backup for 30 days and stays quiet unless something is wrong', function () {
    expect(config('backup.cleanup.default_strategy.keep_all_backups_for_days'))->toBe(30)
        ->and(config('backup.notifications.notifications')[BackupHasFailedNotification::class])->toBe(['mail'])
        ->and(config('backup.notifications.notifications')[BackupWasSuccessfulNotification::class])->toBe([]);
});

/** The schedule as routes/console.php builds it from the current settings. */
function scheduled(): Collection
{
    // A fresh schedule each time; swap() also resets the Schedule facade's cached instance.
    Illuminate\Support\Facades\Schedule::swap($schedule = new Schedule('Asia/Karachi'));
    require base_path('routes/console.php');

    return collect($schedule->events())->mapWithKeys(fn ($e) => [trim(str($e->command)->after('artisan')->replace(["'", '"'], '')) => $e]);
}

function backupSettings(array $values): void
{
    $settings = app(BackupSettings::class);
    foreach ($values as $key => $value) {
        $settings->{$key} = $value;
    }
    $settings->save();
}

it('schedules nightly backups, monitoring and a weekly restore check by default', function () {
    $events = scheduled();

    expect($events->keys()->all())->toContain('sswf:backup', 'sswf:backup --monitor', 'sswf:check-backup')
        ->and($events['sswf:backup']->expression)->toBe('30 1 * * *')
        ->and($events['sswf:backup']->timezone)->toBe('Asia/Karachi')
        ->and($events['sswf:check-backup']->expression)->toBe('0 3 * * 0');
});

it('follows the schedule set in the admin', function () {
    backupSettings(['frequency' => 'weekly', 'weekly_day' => 5, 'time' => '23:15']);
    expect(scheduled()['sswf:backup']->expression)->toBe('15 23 * * 5');

    backupSettings(['frequency' => 'daily', 'time' => '04:05:00']);
    expect(scheduled()['sswf:backup']->expression)->toBe('5 4 * * *');
});

it('stops automatic backups and checks when switched off', function () {
    backupSettings(['schedule_enabled' => false, 'restore_check_enabled' => false]);

    expect(scheduled()->keys()->all())->not->toContain('sswf:backup')->not->toContain('sswf:check-backup');
});

it('reads archive paths the same way on Windows and Linux', function (string $name, string $expected) {
    expect(CheckBackup::normalise($name))->toBe($expected);
})->with([
    ['db-dumps\\mysql-sswf.sql', 'db-dumps/mysql-sswf.sql'],
    ['db-dumps/mysql-sswf.sql', 'db-dumps/mysql-sswf.sql'],
    ['D:\\Sahara\\storage\\app/public\\1\\photo.jpg', 'public/1/photo.jpg'],
    ['/home/forge/subkasahara.org/storage/app/private/9/receipt.pdf', 'private/9/receipt.pdf'],
    ['public/1/photo.jpg', 'public/1/photo.jpg'],
]);

it('refuses to run the restore check on anything but MySQL or MariaDB', function () {
    $this->artisan('sswf:check-backup')
        ->expectsOutputToContain('needs a MySQL or MariaDB database')
        ->assertFailed();
});

// ── Admin → Settings → Backups ────────────────────────────────────────────

function backupAdmin(Role $role = Role::SuperAdmin): User
{
    $user = User::factory()->create();
    $user->assignRole($role->value);

    return $user;
}

it('applies the admin’s retention and alert email to the backup run', function () {
    backupSettings(['keep_days' => 45, 'notify_email' => 'it@example.org', 'frequency' => 'weekly']);

    app(BackupManager::class)->applySettings();

    expect(config('backup.cleanup.default_strategy.keep_all_backups_for_days'))->toBe(45)
        ->and(config('backup.notifications.mail.to'))->toBe('it@example.org')
        ->and(config('backup.monitor_backups.0.health_checks')[MaximumAgeInDays::class])->toBe(8);
});

it('starts a backup and a restore check in the background from the page', function () {
    $this->seed(DatabaseSeeder::class);
    Queue::fake();

    Livewire::actingAs(backupAdmin())->test(ManageBackupSettings::class)
        ->assertSee('Latest backup')
        ->callAction('backupNow')
        ->callAction('checkNow');

    Queue::assertPushed(RunBackup::class);
    Queue::assertPushed(RunRestoreCheck::class);
});

it('saves the schedule from the page', function () {
    $this->seed(DatabaseSeeder::class);

    Livewire::actingAs(backupAdmin())->test(ManageBackupSettings::class)
        ->fillForm(['schedule_enabled' => true, 'frequency' => 'weekly', 'weekly_day' => 3, 'time' => '02:45', 'keep_days' => 60])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = app(BackupSettings::class);
    expect($settings->frequency)->toBe('weekly')
        ->and($settings->weekly_day)->toBe(3)
        ->and($settings->time)->toBe('02:45')
        ->and($settings->keep_days)->toBe(60)
        ->and($settings->cronDescription())->toBe('Every Wednesday at 02:45');
});

it('refuses keeping backups for less than a week', function () {
    $this->seed(DatabaseSeeder::class);

    Livewire::actingAs(backupAdmin())->test(ManageBackupSettings::class)
        ->fillForm(['keep_days' => 2])
        ->call('save')
        ->assertHasFormErrors(['keep_days']);
});

it('lists backups with download links for Super Admin only', function () {
    $this->seed(DatabaseSeeder::class);
    Storage::fake('backups');
    Storage::disk('backups')->put('sswf-website/sswf-2026-11-26-01-30-00.zip', 'zip');

    Livewire::actingAs(backupAdmin())->test(ManageBackupSettings::class)
        ->assertSee('Download');

    $url = URL::temporarySignedRoute('backups.download', now()->addMinutes(5), ['path' => 'sswf-website/sswf-2026-11-26-01-30-00.zip']);
    $this->actingAs(backupAdmin())->get($url)->assertOk()->assertDownload('sswf-2026-11-26-01-30-00.zip');
    $this->actingAs(backupAdmin(Role::Finance))->get($url)->assertForbidden();

    // Only this site's backup files, and only through a signed link.
    $outside = URL::temporarySignedRoute('backups.download', now()->addMinutes(5), ['path' => '../.env']);
    $this->actingAs(backupAdmin())->get($outside)->assertNotFound();
    $this->actingAs(backupAdmin())->get(route('backups.download', ['path' => 'sswf-website/sswf-2026-11-26-01-30-00.zip']))->assertForbidden();
});

it('keeps the Backups page for Super Admin only', function (Role $role) {
    $this->seed(DatabaseSeeder::class);
    $this->actingAs(backupAdmin($role))->get('/admin/manage-backup-settings')->assertForbidden();
})->with([Role::Finance, Role::Editor, Role::Services]);
