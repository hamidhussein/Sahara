<?php

namespace App\Console\Commands;

use App\Settings\BackupSettings;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;
use ZipArchive;

/**
 * Proves the newest backup can be restored (plan 1.4: "a full restore tested").
 *
 * It restores the database dump into a temporary scratch database, compares
 * the important tables with the live ones, checks every uploaded file is in
 * the archive, then removes the scratch database. Nothing live is touched.
 */
#[Signature('sswf:check-backup {--keep : Keep the scratch database for inspection}')]
#[Description('Restore the newest backup into a scratch database and check it is complete')]
class CheckBackup extends Command
{
    /** Tables that must come back from a restore. */
    protected const TABLES = [
        'users', 'donations', 'giving_types', 'causes', 'programs', 'projects', 'project_updates',
        'posts', 'pages', 'partners', 'team_members', 'reports', 'gallery_albums',
        'contact_messages', 'media', 'settings',
    ];

    public function handle(): int
    {
        $connection = config('database.default');
        if (! in_array(config("database.connections.{$connection}.driver"), ['mysql', 'mariadb'], true)) {
            $this->components->error('The restore check needs a MySQL or MariaDB database.');

            return self::FAILURE;
        }

        $disk = config('backup.backup.destination.disks')[0] ?? null;
        $folder = config('backup.backup.name');
        $latest = collect(Storage::disk($disk)->files($folder))
            ->filter(fn ($f) => str_ends_with($f, '.zip'))
            ->sortByDesc(fn ($f) => Storage::disk($disk)->lastModified($f))
            ->first();

        if (! $latest) {
            $this->components->error("No backup found on the [{$disk}] disk. Run php artisan backup:run first.");
            $this->recordResult(['No backup found yet. Make one with “Back up now”.'], null);

            return self::FAILURE;
        }

        $takenAt = now()->setTimestamp(Storage::disk($disk)->lastModified($latest));
        $this->components->info("Checking {$latest} (made {$takenAt->diffForHumans()})");

        $work = storage_path('app/backup-temp/restore-check-'.now()->format('YmdHis'));
        File::ensureDirectoryExists($work);
        $scratch = config("database.connections.{$connection}.database").'_restore_check';
        $problems = [];

        try {
            // 1. Open the archive.
            $zipPath = $work.'/backup.zip';
            File::put($zipPath, Storage::disk($disk)->get($latest));
            $zip = new ZipArchive;
            if ($zip->open($zipPath) !== true) {
                throw new \RuntimeException('The archive could not be opened.');
            }
            if ($password = config('backup.backup.password')) {
                $zip->setPassword($password);
            }

            // Normalised name => name in the archive. Windows writes backslashes and full paths,
            // so names are reduced to "db-dumps/…", "public/…" or "private/…" on every system.
            $entries = collect(range(0, $zip->numFiles - 1))
                ->mapWithKeys(fn ($i) => [static::normalise($zip->getNameIndex($i)) => $zip->getNameIndex($i)]);
            $dumpEntry = $entries->first(fn ($original, $name) => str_starts_with($name, 'db-dumps/') && str_ends_with($name, '.sql'));
            if (! $dumpEntry) {
                throw new \RuntimeException('The archive has no database dump.');
            }

            $dumpFile = $work.'/database.sql';
            $sql = $zip->getFromName($dumpEntry);
            if ($sql === false) {
                throw new \RuntimeException('The database dump could not be read (wrong BACKUP_ARCHIVE_PASSWORD?).');
            }
            File::put($dumpFile, $sql);

            // 2. Restore it into a scratch database.
            DB::connection($connection)->statement("DROP DATABASE IF EXISTS `{$scratch}`");
            DB::connection($connection)->statement("CREATE DATABASE `{$scratch}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->importDump($connection, $scratch, $dumpFile);

            Config::set('database.connections.restore_check', array_merge(config("database.connections.{$connection}"), ['database' => $scratch]));
            DB::purge('restore_check');

            // 3. Compare the tables.
            $rows = [];
            foreach (self::TABLES as $table) {
                $live = DB::connection($connection)->table($table)->where('created_at', '<=', $takenAt)->count();
                $restored = DB::connection('restore_check')->getSchemaBuilder()->hasTable($table)
                    ? DB::connection('restore_check')->table($table)->count()
                    : null;

                $status = match (true) {
                    $restored === null => 'missing',
                    $restored >= $live => 'ok',
                    default => 'fewer rows',
                };
                if ($status !== 'ok') {
                    $problems[] = "Table {$table}: {$status} (live {$live}, restored ".($restored ?? 0).')';
                }
                $rows[] = [$table, $live, $restored ?? '–', $status];
            }
            $this->table(['Table', 'Live (up to backup time)', 'Restored', 'Result'], $rows);

            // 4. Every uploaded file that existed at backup time is in the archive.
            $missingFiles = Media::where('created_at', '<=', $takenAt)->get()
                ->filter(fn (Media $m) => in_array($m->disk, ['public', 'local'], true))
                ->reject(fn (Media $m) => $entries->has(($m->disk === 'public' ? 'public/' : 'private/').str_replace('\\', '/', $m->getPathRelativeToRoot())))
                ->map(fn (Media $m) => $m->getPathRelativeToRoot());

            $this->components->twoColumnDetail('Uploaded files in the archive', $missingFiles->isEmpty() ? '<fg=green>all present</>' : "<fg=red>{$missingFiles->count()} missing</>");
            foreach ($missingFiles->take(10) as $path) {
                $problems[] = "File missing from backup: {$path}";
            }

            $zip->close();
        } catch (Throwable $e) {
            $problems[] = $e->getMessage();
        } finally {
            if (! $this->option('keep')) {
                rescue(fn () => DB::connection($connection)->statement("DROP DATABASE IF EXISTS `{$scratch}`"), report: false);
            }
            DB::purge('restore_check');
            File::deleteDirectory($work);
        }

        $this->recordResult($problems, $latest);

        if ($problems) {
            $this->components->error('The backup could NOT be fully restored:');
            foreach ($problems as $problem) {
                $this->line("  • {$problem}");
            }

            return self::FAILURE;
        }

        $this->components->info('Restore check passed: the newest backup restores completely.');

        return self::SUCCESS;
    }

    /** Shown on Admin → Settings → Backups. */
    protected function recordResult(array $problems, ?string $checked): void
    {
        rescue(function () use ($problems, $checked) {
            $settings = app(BackupSettings::class);
            $settings->last_check_at = now()->toIso8601String();
            $settings->last_check_passed = $problems === [];
            $settings->last_check_summary = $problems === []
                ? 'Restored '.basename((string) $checked).' completely: all tables and files present.'
                : implode("\n", array_slice($problems, 0, 10));
            $settings->save();
        }, report: false);
    }

    /** "D:\Sahara\storage\app/public\1\photo.jpg" or "public/1/photo.jpg" → "public/1/photo.jpg". */
    public static function normalise(string $name): string
    {
        $name = str_replace('\\', '/', $name);
        $marker = '/storage/app/';
        $at = strrpos($name, $marker);

        return $at === false ? ltrim($name, '/') : substr($name, $at + strlen($marker));
    }

    protected function importDump(string $connection, string $database, string $dumpFile): void
    {
        $config = config("database.connections.{$connection}");
        $binary = rtrim((string) ($config['dump']['dump_binary_path'] ?? ''), '/\\');
        $mysql = $binary !== '' ? $binary.'/mysql' : 'mysql';

        $result = Process::timeout(900)
            ->env(['MYSQL_PWD' => (string) $config['password']]) // keeps the password off the command line
            ->input(File::get($dumpFile))
            ->run([$mysql, '--host='.$config['host'], '--port='.$config['port'], '--user='.$config['username'], $database]);

        if (! $result->successful()) {
            throw new \RuntimeException('Importing the database dump failed: '.trim($result->errorOutput()));
        }
    }
}
