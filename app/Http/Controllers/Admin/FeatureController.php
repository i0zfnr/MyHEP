<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SystemFeatures;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeatureController extends Controller
{
    public function index(SystemFeatures $features): View
    {
        return view('admin.features.index', ['features' => $features->all()]);
    }

    public function update(Request $request, SystemFeatures $features, string $feature): RedirectResponse
    {
        $validated = $request->validate(['enabled' => ['required', 'boolean']]);
        $features->set($feature, (bool) $validated['enabled'], (int) session('auth_user.id'));
        auditLog('system_features.update', 'system_features', null, $feature.': '.($validated['enabled'] ? 'enabled' : 'disabled'));

        return redirect()->route('admin.features.index')->with('success', __('Feature availability updated.'));
    }
}
