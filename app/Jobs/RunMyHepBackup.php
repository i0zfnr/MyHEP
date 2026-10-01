<?php

namespace App\Jobs;

use App\Models\BackupRun;
use App\Services\Backups\BackupArchiveName;
use App\Services\Backups\BackupArchiveRunner;
use App\Services\Backups\BackupCapacity;
use App\Services\Backups\BackupSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class RunMyHepBackup implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $timeout = 14400;

    public bool $failOnTimeout = true;

    public function __construct(
        public readonly int $backupRunId,
        public readonly string $lockOwner,
    ) {}

    public function handle(BackupSettings $settings, BackupCapacity $capacity, BackupArchiveRunner $runner): void
    {
        $run = null;
        $filename = null;
        $remotePath = null;
        $remoteVerified = false;
        $size = null;
        $successRecorded = false;

        try {
            $run = BackupRun::query()->find($this->backupRunId);
            if (! $run) {
                return;
            }

            $run->forceFill([
                'status' => 'running',
                'started_at' => now(),
                'error_message' => null,
            ])->save();

            Log::info('MyHEP backup started.', [
                'backup_run_id' => $run->id,
                'type' => $run->type,
                'destination' => 'google_drive',
            ]);

            if (! $settings->isConfigured()) {
                throw new RuntimeException('Backup configuration is incomplete.');
            }

            $capacity->assertEnoughSpace($run->type);

            $filename = BackupArchiveName::make($run->type);
            $configName = $run->type === 'database' ? 'backup_database' : 'backup';
            $packageConfig = config($configName);
            $remotePath = trim((string) $packageConfig['backup']['name'], '/').'/'.$filename;
            $runner->run($run->type, $filename);

            $disk = Storage::disk('google_drive');
            if (! $disk->exists($remotePath)) {
                throw new RuntimeException('The uploaded backup archive could not be verified in Google Drive.');
            }

            $size = (int) $disk->size($remotePath);
            if ($size <= 0) {
                throw new RuntimeException('The uploaded backup archive has no readable size in Google Drive.');
            }
            $remoteVerified = true;

            $run->forceFill([
                'status' => 'successful',
                'destination' => 'google_drive',
                'remote_path' => $remotePath,
                'size_bytes' => $size,
                'error_message' => null,
                'finished_at' => now(),
            ])->save();
            $successRecorded = true;

            try {
                Log::info('MyHEP backup completed and verified.', [
                    'backup_run_id' => $run->id,
                    'type' => $run->type,
                    'size_bytes' => $size,
                    'destination' => 'google_drive',
                    'remote_path' => $remotePath,
                ]);
            } catch (Throwable) {
                // Logging must not invalidate an archive that is already verified and recorded.
            }
        } catch (Throwable $exception) {
            $safeError = $settings->safeError($exception);
            if (! $remoteVerified) {
                $this->removeIncompleteArchive($remotePath, $settings);
            }

            if ($run && ! $successRecorded) {
                try {
                    $run->forceFill([
                        'status' => 'failed',
                        'destination' => 'google_drive',
                        'remote_path' => $remoteVerified ? $remotePath : null,
                        'size_bytes' => $remoteVerified ? $size : null,
                        'error_message' => $safeError,
                        'finished_at' => now(),
                    ])->save();
                } catch (Throwable $recordingException) {
                    Log::error('Could not record the failed MyHEP backup run.', [
                        'backup_run_id' => $run->id,
                        'error' => $settings->safeError($recordingException),
                    ]);
                }
            }

            Log::error('MyHEP backup failed.', [
                'backup_run_id' => $run?->id ?? $this->backupRunId,
                'type' => $run?->type,
                'destination' => 'google_drive',
                'error' => $safeError,
            ]);
        } finally {
            $this->releaseOperationLock();
        }
    }

    public function failed(Throwable $exception): void
    {
        $settings = app(BackupSettings::class);

        try {
            $run = BackupRun::query()->find($this->backupRunId);
            if ($run && ! in_array($run->status, ['successful', 'failed'], true)) {
                $safeError = $settings->safeError($exception);
                $run->forceFill([
                    'status' => 'failed',
                    'error_message' => $safeError,
                    'finished_at' => now(),
                ])->save();

                Log::error('MyHEP backup queue job failed.', [
                    'backup_run_id' => $run->id,
                    'type' => $run->type,
                    'error' => $safeError,
                ]);
            }
        } catch (Throwable $recordingException) {
            Log::error('Could not record a failed MyHEP backup queue job.', [
                'backup_run_id' => $this->backupRunId,
                'error' => $settings->safeError($recordingException),
            ]);
        } finally {
            $this->releaseOperationLock();
        }
    }

    private function removeIncompleteArchive(?string $remotePath, BackupSettings $settings): void
    {
        if (! $remotePath) {
            return;
        }

        try {
            $disk = Storage::disk('google_drive');
            if ($disk->exists($remotePath)) {
                $disk->delete($remotePath);
            }
        } catch (Throwable $exception) {
            Log::warning('Could not remove an incomplete MyHEP backup archive.', [
                'remote_path' => $remotePath,
                'error' => $settings->safeError($exception),
            ]);
        }
    }

    private function releaseOperationLock(): void
    {
        try {
            Cache::restoreLock(
                (string) config('myhep-backups.lock_name', 'myhep.backup-operation'),
                $this->lockOwner,
            )->release();
        } catch (Throwable) {
            // The lock expires automatically if the cache backend has already lost it.
        }
    }
}
