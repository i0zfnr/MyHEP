<?php

namespace App\Services\Backups;

use Throwable;

class BackupSettings
{
    public function isConfigured(): bool
    {
        $password = config('backup.backup.password');

        return collect([
            config('filesystems.disks.google_drive.clientId'),
            config('filesystems.disks.google_drive.clientSecret'),
            config('filesystems.disks.google_drive.refreshToken'),
            config('filesystems.disks.google_drive.folderId'),
            config('backup.backup.password'),
        ])->every(fn ($value): bool => filled($value))
            && is_string($password)
            && mb_strlen($password) >= 32
            && extension_loaded('zip')
            && extension_loaded('openssl');
    }

    public function missingRequirements(): array
    {
        $requirements = [
            'Google Drive client ID' => config('filesystems.disks.google_drive.clientId'),
            'Google Drive client secret' => config('filesystems.disks.google_drive.clientSecret'),
            'Google Drive refresh token' => config('filesystems.disks.google_drive.refreshToken'),
            'Google Drive backup folder ID' => config('filesystems.disks.google_drive.folderId'),
            'Backup archive password' => config('backup.backup.password'),
        ];

        $missing = collect($requirements)
            ->filter(fn ($value): bool => blank($value))
            ->keys()
            ->values()
            ->all();

        $archivePassword = $requirements['Backup archive password'];
        if (filled($archivePassword) && (! is_string($archivePassword) || mb_strlen($archivePassword) < 32)) {
            $missing[] = 'Backup archive password of at least 32 characters';
        }

        if (! extension_loaded('zip')) {
            $missing[] = 'PHP ZIP extension';
        }

        if (! extension_loaded('openssl')) {
            $missing[] = 'PHP OpenSSL extension';
        }

        return $missing;
    }

    public function safeError(Throwable|string $error): string
    {
        $message = $error instanceof Throwable
            ? $error::class.': '.$error->getMessage()
            : $error;

        $secrets = [
            config('filesystems.disks.google_drive.clientId'),
            config('filesystems.disks.google_drive.clientSecret'),
            config('filesystems.disks.google_drive.refreshToken'),
            config('backup.backup.password'),
            config('database.connections.'.config('database.default').'.password'),
        ];

        foreach ($secrets as $secret) {
            if (is_string($secret) && $secret !== '') {
                $message = str_replace($secret, '[redacted]', $message);
            }
        }

        $message = preg_replace('/\bBearer\s+[A-Za-z0-9._~+\/-]+=*/i', 'Bearer [redacted]', $message) ?? $message;

        $message = preg_replace(
            '/(client[_-]?secret|refresh[_-]?token|access[_-]?token|password|authorization)\s*[:=]\s*("[^"]*"|\'[^\']*\'|[^,\s]+)/i',
            '$1=[redacted]',
            $message,
        ) ?? 'An error occurred while processing the backup.';

        return mb_substr(trim($message), 0, 1800);
    }
}
