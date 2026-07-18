<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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

        return view('admin.maintenance.index', compact('maintenance'));
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
}
