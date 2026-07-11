<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminScope
{
    public function handle(Request $request, Closure $next, string $scope): Response
    {
        $authUser = $request->session()->get('auth_user');
        if (!$authUser || ($authUser['role'] ?? null) !== 'admin') {
            return redirect()->route('login');
        }

        $adminRole = $authUser['admin_role'] ?? null;
        $allowed = match ($scope) {
            'scholarship' => ['scholarship_admin', 'system_admin'],
            'discipline' => ['discipline_admin', 'system_admin'],
            'movement' => ['guard', 'discipline_admin', 'system_admin'],
            'backoffice' => ['scholarship_admin', 'discipline_admin', 'system_admin'],
            default => ['system_admin'],
        };

        if (!in_array($adminRole, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
