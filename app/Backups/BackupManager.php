<?php

namespace App\Backups;

use App\Settings\BackupSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;

/**
 * Applies the admin's backup settings to Spatie Laravel Backup and runs it.
 * Used by the scheduler (sswf:backup) and the Backups page buttons.
 */
class BackupManager
{
    public function __construct(protected BackupSettings $settings) {}

    /** Settings from the admin win over config/backup.php for this run. */
    public function applySettings(): void
    {
        Config::set('backup.cleanup.default_strategy.keep_all_backups_for_days', max(1, $this->settings->keep_days));

        if (filled($this->settings->notify_email)) {
            Config::set('backup.notifications.mail.to', $this->settings->notify_email);
        }

        // Weekly backups are "too old" after 8 days, daily ones after 1.
        $maxAge = $this->settings->frequency === 'weekly' ? 8 : 1;
        Config::set('backup.monitor_backups.0.health_checks.'.MaximumAgeInDays::class, $maxAge);
    }

    /** Make a backup now, then remove old ones. Returns a short result for the page. */
    public function backup(): string
    {
        $this->applySettings();

        $exit = Artisan::call('backup:run');
        $output = trim(Artisan::output());

        if ($exit !== 0) {
            return 'Failed: '.str($output)->afterLast("\n")->trim();
        }

        Artisan::call('backup:clean');

        return 'Completed';
    }

    public function monitor(): int
    {
        $this->applySettings();

        return Artisan::call('backup:monitor');
    }

    public function disk(): string
    {
        return config('backup.backup.destination.disks')[0] ?? 'backups';
    }

    /**
     * Backups on the first destination, newest first.
     *
     * @return Collection<int, array{path: string, name: string, size: int, date: Carbon}>
     */
    public function list(): Collection
    {
        $disk = Storage::disk($this->disk());
        $folder = config('backup.backup.name');

        return collect($disk->files($folder))
            ->filter(fn ($path) => str_ends_with($path, '.zip'))
            ->map(fn ($path) => [
                'path' => $path,
                'name' => basename($path),
                'size' => $disk->size($path),
                'date' => now()->setTimestamp($disk->lastModified($path))->timezone('Asia/Karachi'),
            ])
            ->sortByDesc('date')
            ->values();
    }

    /** Only files inside this site's backup folder can be downloaded. */
    public function isBackupFile(string $path): bool
    {
        return str_starts_with($path, config('backup.backup.name').'/')
            && str_ends_with($path, '.zip')
            && ! str_contains($path, '..')
            && Storage::disk($this->disk())->exists($path);
    }
}
