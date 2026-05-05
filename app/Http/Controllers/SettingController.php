<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $authUser = $request->session()->get('auth_user');
        if (!$authUser) {
            return redirect()->route('login');
        }

        $currentLocale = app()->getLocale();
        $backRoute = ($authUser['role'] ?? null) === 'admin' ? 'admin.dashboard' : 'student.dashboard';

        return view('settings.index', compact('currentLocale', 'backRoute'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'in:en,ms'],
        ]);

        $request->session()->put('locale', $validated['locale']);
        app()->setLocale($validated['locale']);

        return redirect()->route('settings.show')->with('success', __('ui.settings_saved'));
    }
}
