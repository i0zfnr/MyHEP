<?php

namespace App\Console\Commands;

use App\Models\BackupRun;
use App\Services\Backups\BackupSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\Notifications\EventHandler;
use Throwable;

class CleanMyHepBackups extends Command
{
    protected $signature = 'myhep:backup-clean';

    protected $description = 'Apply the configured MyHEP backup retention policy.';

    public function handle(BackupSettings $settings): int
    {
        if (! $settings->isConfigured()) {
            $this->warn('Backup cleanup skipped because backup configuration is incomplete.');

            return self::SUCCESS;
        }

        $lock = Cache::lock(
            (string) config('myhep-backups.lock_name', 'myhep.backup-operation'),
            (int) config('myhep-backups.lock_ttl_seconds', 18000),
        );

        if (! $lock->get()) {
            $this->info('Backup cleanup skipped because another backup operation is running.');

            return self::SUCCESS;
        }

        Log::info('MyHEP backup retention cleanup started.', ['destination' => 'google_drive']);
        $failed = false;

        try {
            foreach ([
                'Database' => 'backup_database',
                'Full' => 'backup',
            ] as $folder => $configName) {
                try {
                    $disk = Storage::disk('google_drive');
                    $before = $disk->allFiles($folder);
                    if (count($before) < 2) {
                        Log::info('MyHEP backup retention preserved the only recovery point.', [
                            'folder' => $folder,
                            'backup_count' => count($before),
                        ]);
                        continue;
                    }

                    EventHandler::disable();
                    try {
                        $exitCode = Artisan::call('backup:clean', [
                            '--config' => $configName,
                            '--disable-notifications' => true,
                        ]);
                    } finally {
                        EventHandler::enable();
                    }
                    if ($exitCode !== self::SUCCESS) {
                        $failed = true;
                        Log::error('MyHEP backup retention cleanup failed.', [
                            'folder' => $folder,
                            'error' => 'The backup package returned a failure status.',
                        ]);
                        continue;
                    }

                    $after = $disk->allFiles($folder);
                    $removed = array_values(array_diff($before, $after));
                    foreach ($removed as $path) {
                        Log::info('MyHEP backup retention removed an expired archive.', [
                            'folder' => $folder,
                            'remote_path' => $path,
                        ]);
                    }

                    Log::info('MyHEP backup retention folder cleanup completed.', [
                        'folder' => $folder,
                        'retained_count' => count($after),
                        'removed_count' => count($removed),
                    ]);
                } catch (Throwable $exception) {
                    $failed = true;
                    Log::error('MyHEP backup retention cleanup failed.', [
                        'folder' => $folder,
                        'error' => $settings->safeError($exception),
                    ]);
                }
            }

            $this->pruneOldRunHistory();
            $this->removeStaleTemporaryDirectories();
        } finally {
            $lock->release();
        }

        if ($failed) {
            $this->error('Backup cleanup finished with an error. Check the Backup & Recovery dashboard and Laravel log.');

            return self::FAILURE;
        }

        $this->info('MyHEP backup retention cleanup completed.');

        return self::SUCCESS;
    }

    private function pruneOldRunHistory(): void
    {
        $cutoff = now()->subMonths((int) config('myhep-backups.history_retention_months', 13));
        $deleted = BackupRun::query()
            ->whereIn('status', ['successful', 'failed'])
            ->whereNotNull('finished_at')
            ->where('finished_at', '<', $cutoff)
            ->delete();

        Log::info('MyHEP backup run history retention completed.', [
            'deleted_run_records' => $deleted,
            'cutoff' => $cutoff->toIso8601String(),
        ]);
    }

    private function removeStaleTemporaryDirectories(): void
    {
        $root = storage_path('app/backup-temp');
        if (is_link($root) || ! is_dir($root)) {
            return;
        }

        $cutoff = now()->subHours(24)->getTimestamp();
        foreach (new \DirectoryIterator($root) as $entry) {
            if ($entry->isDot() || $entry->isLink() || ! $entry->isDir() || $entry->getMTime() >= $cutoff) {
                continue;
            }

            // Package temp output is only removed after it has been inactive for a full day.
            \Illuminate\Support\Facades\File::deleteDirectory($entry->getPathname());
            Log::warning('Removed a stale temporary backup directory.', [
                'directory' => $entry->getFilename(),
            ]);
        }
    }
}
