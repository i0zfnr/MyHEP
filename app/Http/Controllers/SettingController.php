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
        $currentAccentTheme = $request->session()->get('accent_theme', 'gold');
        $currentGlassTransparency = (int) $request->session()->get('glass_transparency', 40);
        $canAdjustGlass = $this->canAdjustGlass($authUser);
        $canAdjustAccentTheme = $this->canAdjustGlass($authUser);
        $backRoute = ($authUser['role'] ?? null) === 'admin' ? 'admin.dashboard' : 'student.dashboard';

        $roleMode = [
            'available' => DualRoleSession::canSwitch($request) || DualRoleSession::canSwitchToGeneralStaff($request),
            'student_available' => DualRoleSession::canSwitch($request),
            'general_staff_available' => DualRoleSession::canSwitchToGeneralStaff($request),
            'is_student_mode' => ($authUser['role'] ?? null) === 'student',
            'is_general_staff_mode' => (bool) ($authUser['staff_override'] ?? false),
            'override_enabled' => (bool) ($authUser['admin_override'] ?? false),
        ];
        $sessionOwner = $sessions->owner($request);
        $activeSessions = $sessionOwner
            ? $sessions->sessionsFor($sessionOwner, $request->session()->getId())
            : collect();

        return view('settings.index', compact(
            'currentLocale',
            'currentTheme',
            'currentAccentTheme',
            'currentGlassTransparency',
            'canAdjustGlass',
            'canAdjustAccentTheme',
            'backRoute',
            'roleMode',
            'activeSessions'
        ));
    }

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $authUser = $request->session()->get('auth_user', []);
        $validated = $request->validate([
            'locale' => ['required', 'in:en,ms'],
            'theme' => ['required', 'in:light,dark'],
            'accent_theme' => ['nullable', 'in:gold,candy_blue,lavender,orchid,violet'],
            'glass_transparency' => ['nullable', 'integer', 'min:10', 'max:80'],
        ]);

        if ((array_key_exists('glass_transparency', $validated) || array_key_exists('accent_theme', $validated))
            && ! $this->canAdjustGlass($authUser)) {
            abort(403);
        }

        $request->session()->put('locale', $validated['locale']);
        $request->session()->put('theme', $validated['theme']);
        if (array_key_exists('accent_theme', $validated)) {
            $request->session()->put('accent_theme', $validated['accent_theme']);
        }
        if (array_key_exists('glass_transparency', $validated)) {
            $request->session()->put('glass_transparency', $validated['glass_transparency']);
        }
        app()->setLocale($validated['locale']);

        if ($request->expectsJson()) {
            return response()->json([
                'locale' => $validated['locale'],
                'theme' => $validated['theme'],
                'accent_theme' => $validated['accent_theme'] ?? null,
                'glass_transparency' => $validated['glass_transparency'] ?? null,
            ]);
        }

        return redirect()->route('settings.show')->with('success', __('ui.settings_saved'));
    }

    private function canAdjustGlass(array $authUser): bool
    {
        return ($authUser['role'] ?? null) === 'student'
            || (($authUser['role'] ?? null) === 'admin'
                && ($authUser['admin_role'] ?? null) === 'system_admin');
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
            'mode' => ['required', 'in:student,admin,general_staff'],
            'override' => ['nullable', 'boolean'],
        ]);

        $switched = match ($validated['mode']) {
            'student' => DualRoleSession::switchToStudent($request, (bool) ($validated['override'] ?? false)),
            'general_staff' => DualRoleSession::switchToGeneralStaff($request),
            default => DualRoleSession::switchToAdmin($request),
        };

        if (! $switched) {
            return redirect()->route('settings.show')->withErrors([
                'role_mode' => __('ui.role_mode_unavailable'),
            ]);
        }

        auditLog('auth.role_mode_changed', 'account', null, 'Account access mode changed to '.$validated['mode']);

        return redirect()->route($validated['mode'] === 'student' ? 'student.dashboard' : 'admin.dashboard');
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
