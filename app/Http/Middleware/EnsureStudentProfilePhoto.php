<?php

namespace App\Http\Middleware;

use App\Support\SystemFeatures;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentProfilePhoto
{
    public function __construct(
        private readonly SystemFeatures $features
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->features->enabled('enforce_student_profile_photo')) {
            return $next($request);
        }

        $authUser = session('auth_user');
        if (! is_array($authUser) || ($authUser['role'] ?? null) !== 'student') {
            return $next($request);
        }

        $exemptRoutes = [
            'student.profile',
            'student.profile.update',
            'student.profile.password.update',
            'logout',
            'locale.update',
            'theme.update',
            'settings.show',
            'settings.update',
            'settings.role-mode.update',
            'settings.sessions.destroy',
            'settings.sessions.destroy-others',
            'push.subscribe',
            'push.unsubscribe',
            'notifications.feed',
            'bug-reports.create',
            'bug-reports.store',
        ];

        $currentRoute = (string) ($request->route()?->getName() ?? '');
        if (in_array($currentRoute, $exemptRoutes, true)) {
            return $next($request);
        }

        $studentId = (int) ($authUser['id'] ?? 0);
        if ($studentId <= 0) {
            return $next($request);
        }

        $student = DB::table('students')->where('id', $studentId)->select('photo')->first();
        if ($student && blank($student->photo)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Sila muat naik gambar profil kad matrik yang sah terlebih dahulu.'),
                    'redirect' => route('student.profile'),
                ], 403);
            }

            return redirect()->route('student.profile')
                ->with('warning', __('Sila muat naik gambar profil rasmi standard kad matrik terlebih dahulu untuk mengakses modul sistem.'));
        }

        return $next($request);
    }
}
