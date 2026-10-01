<?php

namespace App\Services\Backups;

use App\Models\BackupRun;
use Carbon\CarbonImmutable;

class BackupHealthService
{
    public function __construct(private readonly BackupSettings $settings) {}

    public function snapshot(bool $driveConnected): array
    {
        $lastSuccessful = BackupRun::query()
            ->where('status', 'successful')
            ->whereNotNull('finished_at')
            ->orderByDesc('finished_at')
            ->first();

        $lastDatabase = BackupRun::query()
            ->where('type', 'database')
            ->where('status', 'successful')
            ->whereNotNull('finished_at')
            ->orderByDesc('finished_at')
            ->first();

        $lastFull = BackupRun::query()
            ->where('type', 'full')
            ->where('status', 'successful')
            ->whereNotNull('finished_at')
            ->orderByDesc('finished_at')
            ->first();

        $latestRun = BackupRun::query()->orderByDesc('id')->first();
        $latestDatabaseAttempt = BackupRun::query()->where('type', 'database')->orderByDesc('id')->first();
        $latestFullAttempt = BackupRun::query()->where('type', 'full')->orderByDesc('id')->first();
        $failedRun = collect([$latestDatabaseAttempt, $latestFullAttempt])
            ->filter(fn (?BackupRun $run): bool => $run?->status === 'failed')
            ->sortByDesc('id')
            ->first();
        $activeRun = BackupRun::query()
            ->whereIn('status', ['queued', 'running'])
            ->orderByDesc('id')
            ->first();

        return [
            'configured' => $this->settings->isConfigured(),
            'last_successful' => $lastSuccessful,
            'last_database' => $lastDatabase,
            'last_full' => $lastFull,
            'latest_run' => $latestRun,
            'failed_run' => $failedRun,
            'active_run' => $activeRun,
            'next_scheduled_at' => $this->nextScheduledAt(),
            'health' => $this->healthState($driveConnected, $failedRun, $activeRun, $lastDatabase, $lastFull),
        ];
    }

    private function healthState(bool $driveConnected, ?BackupRun $failedRun, ?BackupRun $activeRun, ?BackupRun $lastDatabase, ?BackupRun $lastFull): array
    {
        if (! $this->settings->isConfigured()) {
            return ['key' => 'not_configured', 'message' => __('backup.health_not_configured')];
        }

        if (! $driveConnected) {
            return ['key' => 'connection_error', 'message' => __('backup.health_drive_error')];
        }

        if ($activeRun && (! $failedRun || $activeRun->id > $failedRun->id)) {
            $staleAt = $activeRun->status === 'queued'
                ? now()->subMinutes((int) config('myhep-backups.queued_stale_after_minutes', 30))
                : now()->subHours((int) config('myhep-backups.running_stale_after_hours', 6));

            $reference = $activeRun->started_at ?? $activeRun->created_at;
            if ($reference && $reference->lessThan($staleAt)) {
                return ['key' => 'overdue', 'message' => __('backup.health_stalled')];
            }

            return ['key' => 'running', 'message' => __('backup.health_running')];
        }

        if ($failedRun) {
            return ['key' => 'failed', 'message' => __('backup.health_failed')];
        }

        $databaseLimit = (int) config('myhep-backups.database_overdue_after_minutes', 120);
        $fullLimit = (int) config('myhep-backups.full_overdue_after_hours', 27);
        if (! $lastDatabase?->finished_at || $lastDatabase->finished_at->lessThan(now()->subMinutes($databaseLimit))) {
            return ['key' => 'overdue', 'message' => __('backup.health_overdue')];
        }

        if (! $lastFull?->finished_at || $lastFull->finished_at->lessThan(now()->subHours($fullLimit))) {
            return ['key' => 'overdue', 'message' => __('backup.health_overdue')];
        }

        return ['key' => 'healthy', 'message' => __('backup.health_healthy')];
    }

    private function nextScheduledAt(): CarbonImmutable
    {
        $timezone = config('app.timezone', 'Asia/Kuala_Lumpur');
        $now = CarbonImmutable::now($timezone);

        $database = $now->setMinute((int) config('myhep-backups.database_schedule_minute', 10))->setSecond(0);
        if ($database->lessThanOrEqualTo($now)) {
            $database = $database->addHour();
        }

        [$hour, $minute] = array_map('intval', explode(':', (string) config('myhep-backups.full_schedule_time', '00:20')));
        $full = $now->setTime($hour, $minute, 0);
        if ($full->lessThanOrEqualTo($now)) {
            $full = $full->addDay();
        }

        return $database->lessThan($full) ? $database : $full;
    }
}
