<?php

namespace App\Jobs;

use App\Backups\BackupManager;
use App\Settings\BackupSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/** "Back up now" on the Backups page, run in the background. */
class RunBackup implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $timeout = 1800;

    public int $tries = 1;

    /** One backup at a time, even if the button is pressed twice. */
    public int $uniqueFor = 1800;

    public function handle(BackupManager $backups, BackupSettings $settings): void
    {
        $result = $backups->backup();

        $settings->last_manual_at = now()->toIso8601String();
        $settings->last_manual_result = $result;
        $settings->save();
    }
}
