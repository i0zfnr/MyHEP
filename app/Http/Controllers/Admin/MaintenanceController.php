<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function index(): View
    {
        $downFile = storage_path('framework/down');
        $downPayload = is_file($downFile)
            ? json_decode((string) file_get_contents($downFile), true)
            : [];

        $cacheMeta = systemCacheMeta();
        $maintenance = [
            'enabled' => app()->isDownForMaintenance(),
            'cache_enabled' => isSystemCacheEnabled(),
            'cache_last_cleared_at' => $cacheMeta['last_cleared_at'] ?? null,
            'cache_updated_at' => $cacheMeta['updated_at'] ?? null,
            'cache_key_count' => count(systemCacheKeys()),
            'secret' => is_array($downPayload) ? ($downPayload['secret'] ?? null) : null,
            'retry' => is_array($downPayload) ? ($downPayload['retry'] ?? null) : null,
            'refresh' => is_array($downPayload) ? ($downPayload['refresh'] ?? null) : null,
            'redirect' => is_array($downPayload) ? ($downPayload['redirect'] ?? null) : null,
            'bypass_url' => null,
            'server_time' => now()->format('Y-m-d H:i:s'),
        ];

        if (!empty($maintenance['secret'])) {
            $maintenance['bypass_url'] = url($maintenance['secret']);
        }

        $pushSubscriptions = Schema::hasTable('push_subscriptions')
            ? [
                'devices' => DB::table('push_subscriptions')->count(),
                'students' => DB::table('push_subscriptions')->where('user_type', 'student')->distinct()->count('user_id'),
                'admins' => DB::table('push_subscriptions')->where('user_type', 'admin')->distinct()->count('user_id'),
                'current_admin_devices' => DB::table('push_subscriptions')
                    ->where('user_type', 'admin')
                    ->where('user_id', (int) session('auth_user.id'))
                    ->count(),
            ]
            : ['devices' => 0, 'students' => 0, 'admins' => 0, 'current_admin_devices' => 0];

        return view('admin.maintenance.index', compact('maintenance', 'pushSubscriptions'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['enable', 'disable', 'cache_enable', 'cache_disable'])],
        ]);

        if ($validated['action'] === 'enable') {
            $secret = 'myhep-maintenance-' . Str::lower(Str::random(24));
            Artisan::call('down', [
                '--secret' => $secret,
                '--retry' => 60,
            ]);
            auditLog('maintenance.enable', 'system', null, 'Enable maintenance mode');

            return redirect()->route('admin.maintenance.index')
                ->with('success', 'Maintenance mode enabled. Use the bypass URL to continue admin access.');
        }

        if ($validated['action'] === 'cache_enable') {
            setSystemCacheEnabled(true);
            clearSystemCaches();
            auditLog('cache.enable', 'system', null, 'Enable system cache');

            return redirect()->route('admin.maintenance.index')
                ->with('success', 'System cache enabled.');
        }

        if ($validated['action'] === 'cache_disable') {
            setSystemCacheEnabled(false);
            clearSystemCaches();
            auditLog('cache.disable', 'system', null, 'Disable system cache');

            return redirect()->route('admin.maintenance.index')
                ->with('success', 'System cache disabled.');
        }

        Artisan::call('up');
        auditLog('maintenance.disable', 'system', null, 'Disable maintenance mode');

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Maintenance mode disabled. The system is public again.');
    }

    public function testPush(): RedirectResponse
    {
        $adminId = (int) session('auth_user.id');
        $deviceCount = Schema::hasTable('push_subscriptions')
            ? DB::table('push_subscriptions')->where('user_type', 'admin')->where('user_id', $adminId)->count()
            : 0;

        if ($deviceCount === 0) {
            return redirect()->route('admin.maintenance.index')
                ->withErrors(['push' => 'No push-enabled device is registered for your admin account. Enable notifications on this device first.']);
        }

        myhepSendPushNotification('admin', $adminId, [
            'category' => 'account',
            'title' => 'StudentEdge test notification',
            'body' => 'Push notifications are connected to this System Admin account.',
            'url' => route('admin.maintenance.index'),
            'tag' => 'system-admin-push-test-' . now()->timestamp,
        ]);
        auditLog('push.test', 'system', null, "System Admin tested push delivery to {$deviceCount} device(s)");

        return redirect()->route('admin.maintenance.index')
            ->with('success', "Test notification sent to {$deviceCount} registered device(s).");
    }

    public function broadcastMaintenance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'starts_at' => ['required', 'date', 'after_or_equal:now'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'message' => ['nullable', 'string', 'max:300'],
        ]);

        $startsAt = Carbon::parse($validated['starts_at']);
        $endsAt = !empty($validated['ends_at']) ? Carbon::parse($validated['ends_at']) : null;
        $schedule = $startsAt->format('d M Y, h:i A');
        if ($endsAt) {
            $schedule .= ' until ' . $endsAt->format('d M Y, h:i A');
        }
        $body = filled($validated['message'] ?? null)
            ? trim((string) $validated['message'])
            : "StudentEdge is scheduled for maintenance from {$schedule}. Please save your work before maintenance begins.";

        $studentIds = myhepPushSubscribedUserIds('student');
        $adminIds = myhepPushSubscribedUserIds('admin');
        if ($studentIds === [] && $adminIds === []) {
            return redirect()->route('admin.maintenance.index')
                ->withErrors(['push' => 'No subscribed student or admin devices are available for this broadcast.']);
        }

        $message = [
            'category' => 'account',
            'title' => 'Scheduled System Maintenance',
            'body' => $body,
            'url' => '/',
            'tag' => 'system-maintenance-' . $startsAt->format('YmdHi'),
            'requireInteraction' => true,
            'ttl' => 86400,
        ];
        myhepSendPushToAllStudents($message);
        myhepSendPushToAllAdmins($message);

        $recipientCount = count($studentIds) + count($adminIds);
        auditLog('push.maintenance_broadcast', 'system', null, "Maintenance push sent to {$recipientCount} account(s); starts {$startsAt->toIso8601String()}");

        return redirect()->route('admin.maintenance.index')
            ->with('success', "Maintenance notification sent to {$recipientCount} subscribed account(s).");
    }
}
