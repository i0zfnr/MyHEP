<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminScope
{
    public function handle(Request $request, Closure $next, string $scope): Response
    {
        $authUser = $request->session()->get('auth_user');
        if (!$authUser || ($authUser['role'] ?? null) !== 'admin') {
            return redirect()->route('login');
        }

        $admin = DB::table('admins')
            ->select('id', 'role')
            ->where('id', $authUser['id'] ?? 0)
            ->first();

        if (!$admin || ($admin->role ?? null) !== ($authUser['admin_role'] ?? null)) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        $adminRole = $admin->role;
        $allowed = match ($scope) {
            'scholarship' => ['scholarship_admin', 'student_affairs_head', 'system_admin'],
            'discipline' => ['discipline_admin', 'student_affairs_head', 'system_admin'],
            'students' => ['scholarship_admin', 'discipline_admin', 'student_affairs_head', 'guard', 'system_admin'],
            'movement' => ['guard', 'discipline_admin', 'student_affairs_head', 'system_admin'],
            'backoffice' => ['scholarship_admin', 'discipline_admin', 'student_affairs_head', 'system_admin'],
            default => ['system_admin'],
        };

        if (!in_array($adminRole, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
