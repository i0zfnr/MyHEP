<?php

namespace App\Http\Controllers\Admin;

use App\Models\BackupRun;
use App\Services\Backups\BackupDispatcher;
use App\Services\Backups\BackupHealthService;
use App\Services\Backups\BackupSettings;
use App\Services\Backups\GoogleDriveConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BackupController
{
    public function index(GoogleDriveConnection $drive, BackupHealthService $health): View
    {
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
        ]);
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
