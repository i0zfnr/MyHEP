<?php

namespace App\Http\Controllers\Admin;

use App\Models\BackupRun;
use App\Services\Backups\BackupDispatcher;
use App\Services\Backups\BackupHealthService;
use App\Services\Backups\BackupSettings;
use App\Services\Backups\GoogleDriveConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BackupController
{
    public function index(GoogleDriveConnection $drive, BackupHealthService $health, BackupSettings $settings): View
    {
        $settings->applyStoredConfig();
        $connection = $drive->status();
        $snapshot = $health->snapshot($connection['connected']);
        $recentBackups = BackupRun::query()
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('admin.backups.index', [
            'connection' => $connection,
            'snapshot' => $snapshot,
            'recentBackups' => $recentBackups,
            'retention' => [
                'hourly' => __('backup.retention_hourly'),
                'daily' => __('backup.retention_daily'),
                'monthly' => __('backup.retention_monthly'),
            ],
            'missingRequirements' => app(BackupSettings::class)->missingRequirements(),
            'backupSettings' => $settings->formState(),
        ]);
    }

    public function updateSettings(Request $request, BackupSettings $settings): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'notification_email' => ['nullable', 'email', 'max:255'],
            'google_drive_client_id' => ['nullable', 'string', 'max:1024'],
            'google_drive_client_secret' => ['nullable', 'string', 'max:8192'],
            'google_drive_refresh_token' => ['nullable', 'string', 'max:8192'],
            'google_drive_folder_id' => ['nullable', 'string', 'max:1024'],
            'archive_password' => ['nullable', 'string', 'min:32', 'max:4096'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only([
                    'notification_email',
                    'google_drive_client_id',
                    'google_drive_folder_id',
                ]));
        }

        $settings->saveFromAdmin($validator->validated(), (int) $request->session()->get('auth_user.id', 0));
        auditLog('backup_settings.update', 'backup_settings', 1, 'Backup connection and notification settings updated.');

        return redirect()->route('admin.backups.index')->with('success', __('backup.settings_saved'));
    }

    public function store(
        Request $request,
        BackupDispatcher $dispatcher,
        BackupSettings $settings,
        GoogleDriveConnection $drive,
    ): RedirectResponse {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['database', 'full'])],
        ]);

        if (! $settings->isConfigured()) {
            return back()->with('error', __('backup.manual_missing_config', [
                'requirements' => implode(', ', $settings->missingRequirements()),
            ]));
        }

        if (! $drive->status()['connected']) {
            return back()->with('error', __('backup.manual_drive_error'));
        }

        $result = $dispatcher->enqueue(
            $validated['type'],
            'admin',
            (int) $request->session()->get('auth_user.id', 0),
        );

        if ($result['reason'] === 'already_running') {
            return back()->with('error', __('backup.manual_already_running'));
        }

        if ($result['reason'] !== null || ! $result['run']) {
            return back()->with('error', __('backup.manual_queue_error'));
        }

        return back()->with('success', __('backup.manual_queued', [
            'type' => $validated['type'] === 'database' ? __('backup.type_database') : __('backup.type_full'),
        ]));
    }
}
