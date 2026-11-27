<?php

namespace App\Console\Commands;

use App\Backups\BackupManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('sswf:backup {--monitor : Only check the backups are fresh and within size}')]
#[Description('Make a backup and clean old ones, using the settings from Admin → Settings → Backups')]
class RunScheduledBackup extends Command
{
    public function handle(BackupManager $backups): int
    {
        if ($this->option('monitor')) {
            return $backups->monitor();
        }

        $result = $backups->backup();
        $this->line($result);

        return str_starts_with($result, 'Failed') ? self::FAILURE : self::SUCCESS;
    }
}
