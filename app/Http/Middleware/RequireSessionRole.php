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
            ? DB::table('admins')->select('id', 'role')->where('id', $authUser['id'] ?? 0)->first()
            : DB::table('students')->select('id')->where('id', $authUser['id'] ?? 0)->first();

        if (!$account || ($role === 'admin' && ($account->role ?? null) !== ($authUser['admin_role'] ?? null))) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        if (!empty($authUser['linked_admin_id']) && !DualRoleSession::linkedAdmin($request)) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        if ($role === 'student' && $this->studentProfileIncomplete((int) ($authUser['id'] ?? 0)) && !$this->isAllowedProfileRoute($request)) {
            return redirect()->route('student.profile')
                ->withErrors(['profile' => __('Please complete your profile and upload a profile photo before using the system.')]);
        }

        return $next($request);
    }

    private function studentProfileIncomplete(int $studentId): bool
    {
        if ($studentId <= 0 || !Schema::hasTable('students')) {
            return false;
        }

        $columns = ['photo'];

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
}
