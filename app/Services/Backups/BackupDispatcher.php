<?php

namespace App\Services\Backups;

use App\Jobs\RunMyHepBackup;
use App\Models\BackupRun;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class BackupDispatcher
{
    public function __construct(private readonly BackupSettings $settings) {}

    public function enqueue(string $type, string $triggeredBy, ?int $adminId = null): array
    {
        if (! in_array($type, ['database', 'full'], true)) {
            return ['run' => null, 'reason' => 'invalid_type'];
        }

        if (! $this->settings->isConfigured()) {
            return ['run' => null, 'reason' => 'not_configured'];
        }

        $lockName = (string) config('myhep-backups.lock_name', 'myhep.backup-operation');
        $lockTtl = (int) config('myhep-backups.lock_ttl_seconds', 18000);
        $lock = Cache::lock($lockName, $lockTtl);

        if (! $lock->get()) {
            return ['run' => null, 'reason' => 'already_running'];
        }

        $run = null;
        try {
            $run = BackupRun::query()->create([
                'type' => $type,
                'triggered_by' => $triggeredBy,
                'admin_id' => $adminId,
                'status' => 'queued',
                'destination' => 'google_drive',
            ]);

            $job = (new RunMyHepBackup($run->id, $lock->owner()))
                ->onConnection((string) config('myhep-backups.queue_connection', 'backups'))
                ->onQueue((string) config('myhep-backups.queue_name', 'backups'));

            Bus::dispatch($job);

            Log::info('MyHEP backup queued.', [
                'backup_run_id' => $run->id,
                'type' => $type,
                'triggered_by' => $triggeredBy,
                'destination' => 'google_drive',
            ]);

            return ['run' => $run->fresh(), 'reason' => null];
        } catch (Throwable $exception) {
            $safeError = $this->settings->safeError($exception);
            try {
                $run?->forceFill([
                    'status' => 'failed',
                    'error_message' => $safeError,
                    'finished_at' => now(),
                ])->save();
            } finally {
                $lock->release();
            }

            Log::error('MyHEP backup could not be queued.', [
                'backup_run_id' => $run?->id,
                'type' => $type,
                'error' => $safeError,
            ]);

            return ['run' => $run?->fresh(), 'reason' => 'queue_failed'];
        }
    }
}
