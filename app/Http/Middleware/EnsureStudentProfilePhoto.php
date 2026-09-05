<?php

namespace App\Http\Middleware;

use App\Support\SystemFeatures;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        if (! Schema::hasTable('students') || ! Schema::hasColumn('students', 'photo')) {
            return $next($request);
        }

        $hasPhotoStatus = Schema::hasColumn('students', 'profile_photo_status');
        $select = ['photo'];
        if ($hasPhotoStatus) {
            $select[] = 'profile_photo_status';
        }
        if (Schema::hasColumn('students', 'profile_completion_bypass')) {
            $select[] = 'profile_completion_bypass';
        }

        $student = DB::table('students')->where('id', $studentId)->select($select)->first();
        if ((bool) ($student->profile_completion_bypass ?? false)) {
            return $next($request);
        }
        $status = $hasPhotoStatus
            ? (string) ($student->profile_photo_status ?? '')
            : '';
        $photoApproved = $hasPhotoStatus ? $status === 'approved' : filled($student->photo ?? null);

        if ($student && (blank($student->photo) || ! $photoApproved)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Sila muat naik gambar profil kad matrik yang sah dan tunggu kelulusan admin terlebih dahulu.'),
                    'redirect' => route('student.profile'),
                ], 403);
            }

            return redirect()->route('student.profile')
                ->with('warning', __('Sila muat naik gambar profil rasmi standard kad matrik dan tunggu kelulusan admin terlebih dahulu untuk mengakses modul sistem.'));
        }

        return $next($request);
    }
}
