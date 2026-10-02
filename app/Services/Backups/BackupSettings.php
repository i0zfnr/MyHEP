<?php

namespace App\Services\Backups;

use App\Models\BackupSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackupSettings
{
    private const NOTIFICATIONS_DISABLED_ADDRESS = 'backup-notifications-disabled@example.invalid';

    private static bool $defaultsCaptured = false;

    private static array $defaults = [];

    public function applyStoredConfig(): void
    {
        if (! self::$defaultsCaptured) {
            self::$defaults = [
                'filesystems.disks.google_drive.clientId' => config('filesystems.disks.google_drive.clientId'),
                'filesystems.disks.google_drive.clientSecret' => config('filesystems.disks.google_drive.clientSecret'),
                'filesystems.disks.google_drive.refreshToken' => config('filesystems.disks.google_drive.refreshToken'),
                'filesystems.disks.google_drive.folderId' => config('filesystems.disks.google_drive.folderId'),
                'backup.backup.password' => config('backup.backup.password'),
                'backup_database.backup.password' => config('backup_database.backup.password'),
                'backup.backup.notifications.mail.to' => config('backup.backup.notifications.mail.to'),
                'backup_database.notifications.mail.to' => config('backup_database.notifications.mail.to'),
            ];
            self::$defaultsCaptured = true;
        }

        $effective = self::$defaults;
        if (Schema::hasTable('backup_settings')) {
            $setting = BackupSetting::query()->find(1);
            if ($setting) {
                foreach ([
                    'google_drive_client_id' => 'filesystems.disks.google_drive.clientId',
                    'google_drive_client_secret' => 'filesystems.disks.google_drive.clientSecret',
                    'google_drive_refresh_token' => 'filesystems.disks.google_drive.refreshToken',
                    'google_drive_folder_id' => 'filesystems.disks.google_drive.folderId',
                ] as $field => $configKey) {
                    $effective[$configKey] = filled($setting->{$field})
                        ? $setting->{$field}
                        : self::$defaults[$configKey];
                }

                $archivePassword = filled($setting->archive_password)
                    ? $setting->archive_password
                    : self::$defaults['backup.backup.password'];
                $effective['backup.backup.password'] = $archivePassword;
                $effective['backup_database.backup.password'] = filled($setting->archive_password)
                    ? $setting->archive_password
                    : self::$defaults['backup_database.backup.password'];

                $email = filled($setting->notification_email)
                    ? $setting->notification_email
                    : self::$defaults['backup.backup.notifications.mail.to'];
                $effective['backup.backup.notifications.mail.to'] = $email;
                $effective['backup_database.notifications.mail.to'] = filled($setting->notification_email)
                    ? $setting->notification_email
                    : self::$defaults['backup_database.notifications.mail.to'];
            }
        }

        config($effective);
        Storage::forgetDisk('google_drive');
    }

    public function saveFromAdmin(array $values, int $adminId): void
    {
        $setting = BackupSetting::query()->find(1) ?? new BackupSetting;
        $setting->id = 1;

        foreach ([
            'google_drive_client_id',
            'google_drive_folder_id',
            'notification_email',
        ] as $field) {
            if (array_key_exists($field, $values)) {
                $value = trim((string) ($values[$field] ?? ''));
                $setting->{$field} = $value === '' ? null : $value;
            }
        }

        // Secret inputs are intentionally blank in the form. A blank value keeps
        // the encrypted value already stored instead of replacing it with empty text.
        foreach ([
            'google_drive_client_secret',
            'google_drive_refresh_token',
            'archive_password',
        ] as $field) {
            $value = trim((string) ($values[$field] ?? ''));
            if ($value !== '') {
                $setting->{$field} = $value;
            }
        }

        $setting->updated_by = $adminId;
        $setting->save();
        $this->applyStoredConfig();
    }

    public function formState(): array
    {
        $this->applyStoredConfig();

        return [
            'google_drive_client_id' => (string) config('filesystems.disks.google_drive.clientId', ''),
            'google_drive_folder_id' => (string) config('filesystems.disks.google_drive.folderId', ''),
            'notification_email' => $this->notificationEmail() ?? '',
            'client_secret_configured' => filled(config('filesystems.disks.google_drive.clientSecret')),
            'refresh_token_configured' => filled(config('filesystems.disks.google_drive.refreshToken')),
            'archive_password_configured' => filled(config('backup.backup.password')),
        ];
    }

    public function notificationEmail(): ?string
    {
        $this->applyStoredConfig();
        $email = trim((string) config('backup.backup.notifications.mail.to', ''));

        return $email !== self::NOTIFICATIONS_DISABLED_ADDRESS && filter_var($email, FILTER_VALIDATE_EMAIL)
            ? $email
            : null;
    }

    public function isConfigured(): bool
    {
        $this->applyStoredConfig();
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
        $this->applyStoredConfig();
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
        $this->applyStoredConfig();
        $message = $error instanceof Throwable
            ? $error::class.': '.$error->getMessage()
            : $error;

        $secrets = [
            config('filesystems.disks.google_drive.clientId'),
            config('filesystems.disks.google_drive.clientSecret'),
            config('filesystems.disks.google_drive.refreshToken'),
            config('filesystems.disks.google_drive.folderId'),
            config('backup.backup.password'),
            config('backup.backup.notifications.mail.to'),
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
