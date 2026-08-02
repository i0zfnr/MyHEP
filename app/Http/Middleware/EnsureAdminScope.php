<?php

namespace App\Http\Middleware;

use App\Support\AdminPermissions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminScope
{
    public function __construct(private readonly AdminPermissions $permissions) {}

    public function handle(Request $request, Closure $next, string $scope): Response
    {
        $authUser = $request->session()->get('auth_user');
        if (! $authUser || ($authUser['role'] ?? null) !== 'admin') {
            return redirect()->route('login');
        }

        $admin = DB::table('admins')
            ->select('id', 'role')
            ->where('id', $authUser['id'] ?? 0)
            ->first();

        if (! $admin || ($admin->role ?? null) !== ($authUser['admin_role'] ?? null)) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        if (! $this->permissions->allowsRole($admin->role, $scope)) {
            abort(403);
        }

        return $next($request);
    }
}
