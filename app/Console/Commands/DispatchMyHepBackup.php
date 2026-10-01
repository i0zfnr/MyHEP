<?php

namespace App\Console\Commands;

use App\Services\Backups\BackupDispatcher;
use App\Services\Backups\BackupSettings;
use Illuminate\Console\Command;

class DispatchMyHepBackup extends Command
{
    protected $signature = 'myhep:backup-dispatch {type=full : database or full} {--scheduled : Mark this as a scheduled backup}';

    protected $description = 'Queue an encrypted MyHEP backup to Google Drive.';

    public function handle(BackupDispatcher $dispatcher, BackupSettings $settings): int
    {
        $type = (string) $this->argument('type');
        if (! in_array($type, ['database', 'full'], true)) {
            $this->error('Backup type must be database or full.');

            return self::INVALID;
        }

        $scheduled = (bool) $this->option('scheduled');
        if (! $settings->isConfigured()) {
            $this->warn('Backup dispatch skipped. Missing configuration: '.implode(', ', $settings->missingRequirements()).'.');

            return $scheduled ? self::SUCCESS : self::FAILURE;
        }

        $result = $dispatcher->enqueue($type, $scheduled ? 'scheduler' : 'cli');
        if ($result['reason'] === 'already_running') {
            $this->info('A backup or backup cleanup is already running; no duplicate was queued.');

            return self::SUCCESS;
        }

        if ($result['reason'] !== null) {
            $this->error('The backup could not be queued. Check the Backup & Recovery dashboard and Laravel log.');

            return self::FAILURE;
        }

        $this->info('Queued MyHEP '.$type.' backup run '.$result['run']->id.'.');

        return self::SUCCESS;
    }
}
