<?php

namespace App\Services\Backups;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GoogleDriveConnection
{
    public function __construct(private readonly BackupSettings $settings) {}

    public function status(): array
    {
        if (! $this->settings->isConfigured()) {
            return [
                'connected' => false,
                'message' => __('backup.drive_not_configured'),
            ];
        }

        try {
            // Lists Drive metadata only. It never reads any backup contents.
            Storage::disk('google_drive')->directories('');

            return [
                'connected' => true,
                'message' => __('backup.drive_connected'),
            ];
        } catch (Throwable $exception) {
            $safeError = $this->settings->safeError($exception);
            Log::warning('MyHEP Google Drive backup connection failed.', [
                'error' => $safeError,
            ]);

            return [
                'connected' => false,
                'message' => $safeError,
            ];
        }
    }
}
