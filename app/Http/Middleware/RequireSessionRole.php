<?php

namespace App\Http\Middleware;

use App\Support\DualRoleSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class RequireSessionRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $authUser = $request->session()->get('auth_user');
        if (!$authUser || ($authUser['role'] ?? null) !== $role) {
            return redirect()->route('login');
        }

        $account = $role === 'admin'
            ? DB::table('admins')->select('id', 'role', 'full_name')->where('id', $authUser['id'] ?? 0)->first()
            : DB::table('students')->select('id', 'full_name')->where('id', $authUser['id'] ?? 0)->first();

        $isStaffOverride = $role === 'admin' && (bool) ($authUser['staff_override'] ?? false);
        if (!$account || ($role === 'admin' && !$isStaffOverride && ($account->role ?? null) !== ($authUser['admin_role'] ?? null))) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        if (!empty($authUser['linked_admin_id']) && !DualRoleSession::linkedAdmin($request)) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        $currentName = trim((string) ($account->full_name ?? ''));
        if ($currentName !== '' && ($authUser['name'] ?? null) !== $currentName) {
            $authUser['name'] = $currentName;
            $request->session()->put('auth_user', $authUser);
        }

        if ($role === 'student') {
            $studentId = (int) ($authUser['id'] ?? 0);

            if ($this->studentProfileIncomplete($studentId) && !$this->isAllowedProfileRoute($request)) {
                return redirect()->route('student.profile')
                    ->withErrors(['profile' => __('Please complete all required profile and guardian information before using the system.')]);
            }

            if (!$this->studentProfileIncomplete($studentId)
                && !$this->studentScholarshipStatusComplete($studentId)
                && !$this->isAllowedOnboardingRoute($request)) {
                return redirect()->route('student.scholarship-status.form')
                    ->withErrors(['scholarship_status' => __('Please complete the scholarship status form before using the system.')]);
            }
        }

        return $next($request);
    }

    private function studentProfileIncomplete(int $studentId): bool
    {
        if ($studentId <= 0 || !Schema::hasTable('students')) {
            return false;
        }

        // Residence status, room number and current study address are intentionally optional.
        $columns = [
            'photo', 'email', 'semester', 'academic_session', 'phone', 'address',
            'religion', 'parliament', 'dun', 'race', 'date_of_birth',
            'guardian_name', 'guardian_ic_no', 'guardian_address', 'guardian_phone',
            'mother_ic_no', 'guardian_occupation', 'family_income', 'oku_status',
        ];

        $select = array_values(array_filter($columns, fn (string $column) => Schema::hasColumn('students', $column)));
        if ($select === []) {
            return false;
        }

        $student = DB::table('students')
            ->select($select)
            ->where('id', $studentId)
            ->first();

        if (!$student) {
            return false;
        }

        foreach ($select as $column) {
            if (blank($student->{$column} ?? null)) {
                return true;
            }
        }

        return false;
    }

    private function studentScholarshipStatusComplete(int $studentId): bool
    {
        if ($studentId <= 0 || !Schema::hasTable('student_scholarship_status_forms')) {
            return true;
        }

        return DB::table('student_scholarship_status_forms')
            ->where('student_id', $studentId)
            ->whereNotNull('submitted_at')
            ->exists();
    }

    private function isAllowedProfileRoute(Request $request): bool
    {
        return $request->routeIs(
            'student.profile',
            'student.profile.update',
            'student.profile.password.update',
            'logout',
            'settings.show',
            'settings.update',
            'notifications.feed',
            'push.subscribe',
            'push.unsubscribe',
        );
    }

    private function isAllowedOnboardingRoute(Request $request): bool
    {
        return $this->isAllowedProfileRoute($request) || $request->routeIs(
            'student.scholarship-status.form',
            'student.scholarship-status.submit',
        );
    }
}
