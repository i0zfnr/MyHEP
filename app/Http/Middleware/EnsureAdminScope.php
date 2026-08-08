<?php

namespace App\Http\Middleware;

use App\Support\AdminPermissions;
use App\Support\DualRoleSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        $adminQuery = DB::table('admins')->select('id', 'role');
        if (Schema::hasColumn('admins', 'staff_category')) {
            $adminQuery->addSelect('staff_category');
        }
        if (Schema::hasColumn('admins', 'is_active')) {
            $adminQuery->addSelect('is_active');
        }
        $admin = $adminQuery->where('id', $authUser['id'] ?? 0)->first();

        $isStaffOverride = (bool) ($authUser['staff_override'] ?? false);
        $sessionCategory = $authUser['staff_category'] ?? null;
        $databaseCategory = $admin->staff_category ?? null;
        if (! $admin
            || ! (bool) ($admin->is_active ?? true)
            || ($isStaffOverride && ! DualRoleSession::linkedAdmin($request))
            || (! $isStaffOverride && ($admin->role ?? null) !== ($authUser['admin_role'] ?? null))
            || (! $isStaffOverride && ($admin->role ?? null) === 'lecturer' && $databaseCategory !== $sessionCategory)) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        $effectiveRole = $isStaffOverride ? ($authUser['admin_role'] ?? null) : $admin->role;
        $effectiveCategory = $isStaffOverride ? $sessionCategory : ($admin->staff_category ?? null);
        if (! $this->permissions->allowsAccount($effectiveRole, $effectiveCategory, $scope)) {
            abort(403);
        }

        return $next($request);
    }
}
