<?php

namespace App\Http\Controllers;

use App\Support\DualRoleSession;
use Illuminate\Http\JsonResponse;
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
        $currentTheme = $request->session()->get('theme', 'light');
        $backRoute = ($authUser['role'] ?? null) === 'admin' ? 'admin.dashboard' : 'student.dashboard';

        $roleMode = [
            'available' => DualRoleSession::canSwitch($request),
            'is_student_mode' => ($authUser['role'] ?? null) === 'student',
            'override_enabled' => (bool) ($authUser['admin_override'] ?? false),
        ];

        return view('settings.index', compact('currentLocale', 'currentTheme', 'backRoute', 'roleMode'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'in:en,ms'],
            'theme' => ['required', 'in:light,dark'],
        ]);

        $request->session()->put('locale', $validated['locale']);
        $request->session()->put('theme', $validated['theme']);
        app()->setLocale($validated['locale']);

        return redirect()->route('settings.show')->with('success', __('ui.settings_saved'));
    }

    public function updateTheme(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'in:light,dark'],
        ]);

        $request->session()->put('theme', $validated['theme']);

        if ($request->expectsJson()) {
            return response()->json(['theme' => $validated['theme']]);
        }

        return redirect()->back();
    }

    public function updateRoleMode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:student,admin'],
            'override' => ['nullable', 'boolean'],
        ]);

        $switched = $validated['mode'] === 'student'
            ? DualRoleSession::switchToStudent($request, (bool) ($validated['override'] ?? false))
            : DualRoleSession::switchToAdmin($request);

        if (!$switched) {
            return redirect()->route('settings.show')->withErrors([
                'role_mode' => __('ui.role_mode_unavailable'),
            ]);
        }

        auditLog('auth.role_mode_changed', 'account', null, 'Account access mode changed to ' . $validated['mode']);

        return redirect()->route($validated['mode'] === 'admin' ? 'admin.dashboard' : 'student.dashboard');
    }
}
