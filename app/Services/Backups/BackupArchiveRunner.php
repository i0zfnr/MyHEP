<?php

namespace App\Services\Backups;

use RuntimeException;
use Spatie\Backup\Config\Config as BackupPackageConfig;
use Spatie\Backup\Notifications\EventHandler;
use Spatie\Backup\Tasks\Backup\BackupJob;
use Spatie\Backup\Tasks\Backup\BackupJobFactory;

class BackupArchiveRunner
{
    public function run(string $type, string $filename): void
    {
        $configName = match ($type) {
            'database' => 'backup_database',
            'full' => 'backup',
            default => throw new RuntimeException('Unsupported backup type.'),
        };
        $packageConfig = config($configName);

        if (($packageConfig['backup']['encryption'] ?? null) !== 'aes256'
            || blank($packageConfig['backup']['password'] ?? null)) {
            throw new RuntimeException('Encrypted backup configuration is unavailable.');
        }

        $backup = $this->makeBackupJob(BackupPackageConfig::fromArray($packageConfig))
            ->setFilename($filename);

        if ($type === 'database') {
            $backup->dontBackupFilesystem();
        }

        // MyHEP records outcomes itself; an unconfigured package notification recipient
        // must not turn a completed archive upload into a failed run.
        EventHandler::disable();
        try {
            $backup->run();
        } finally {
            EventHandler::enable();
        }
    }

    protected function makeBackupJob(BackupPackageConfig $config): BackupJob
    {
        return BackupJobFactory::createFromConfig($config)->disableSignals();
    }
}
