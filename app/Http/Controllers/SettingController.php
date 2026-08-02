<?php

namespace App\Http\Controllers;

use App\Support\AccountSessionManager;
use App\Support\DualRoleSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function show(Request $request, AccountSessionManager $sessions): View|RedirectResponse
    {
        $authUser = $request->session()->get('auth_user');
        if (! $authUser) {
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
        $sessionOwner = $sessions->owner($request);
        $activeSessions = $sessionOwner
            ? $sessions->sessionsFor($sessionOwner, $request->session()->getId())
            : collect();

        return view('settings.index', compact('currentLocale', 'currentTheme', 'backRoute', 'roleMode', 'activeSessions'));
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

        if (! $switched) {
            return redirect()->route('settings.show')->withErrors([
                'role_mode' => __('ui.role_mode_unavailable'),
            ]);
        }

        auditLog('auth.role_mode_changed', 'account', null, 'Account access mode changed to '.$validated['mode']);

        return redirect()->route($validated['mode'] === 'admin' ? 'admin.dashboard' : 'student.dashboard');
    }

    public function destroySession(Request $request, AccountSessionManager $sessions, string $publicId): RedirectResponse
    {
        $owner = $sessions->owner($request);
        abort_unless($owner, 403);

        $result = $sessions->revoke($owner, $publicId, $request->session()->getId());
        abort_if($result === 'not_found', 404);

        if ($result === 'current') {
            return redirect()->route('settings.show')->withErrors([
                'session' => __('ui.cannot_revoke_current_session'),
            ]);
        }

        auditLog('auth.session_revoked', $owner['type'], $owner['id'], 'Another account session was revoked');

        return redirect()->route('settings.show')->with('success', __('ui.session_revoked'));
    }

    public function destroyOtherSessions(Request $request, AccountSessionManager $sessions): RedirectResponse
    {
        $owner = $sessions->owner($request);
        abort_unless($owner, 403);

        $count = $sessions->revokeOthers($owner, $request->session()->getId());
        auditLog('auth.other_sessions_revoked', $owner['type'], $owner['id'], "Revoked {$count} other account sessions");

        return redirect()->route('settings.show')->with('success', __('ui.other_sessions_revoked'));
    }
}
