<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SystemFeatures;
use App\Support\SystemSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeatureController extends Controller
{
    public function index(SystemFeatures $features, SystemSettings $settings): View
    {
        return view('admin.features.index', [
            'features' => $features->all(),
            'sessionLifetimeDays' => (int) ceil($settings->sessionLifetime() / 1440),
        ]);
    }

    public function update(Request $request, SystemFeatures $features, string $feature): RedirectResponse
    {
        $validated = $request->validate(['enabled' => ['required', 'boolean']]);
        $features->set($feature, (bool) $validated['enabled'], (int) session('auth_user.id'));
        auditLog('system_features.update', 'system_features', null, $feature.': '.($validated['enabled'] ? 'enabled' : 'disabled'));

        return redirect()->route('admin.features.index')->with('success', __('Feature availability updated.'));
    }

    public function updateSessionLifetime(Request $request, SystemSettings $settings): RedirectResponse
    {
        $validated = $request->validate([
            'session_lifetime_days' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        $minutes = (int) $validated['session_lifetime_days'] * 1440;
        $settings->setSessionLifetime($minutes, (int) session('auth_user.id'));
        auditLog('system_settings.session_lifetime', 'system_settings', null, 'Session idle timeout: '.$validated['session_lifetime_days'].' days');

        return redirect()->route('admin.features.index')->with('success', __('Session timeout updated.'));
    }
}
