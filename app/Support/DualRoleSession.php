<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DualRoleSession
{
    public static function linkedAdmin(Request $request): ?object
    {
        $authUser = $request->session()->get('auth_user', []);
        $adminId = ($authUser['role'] ?? null) === 'admin'
            ? ($authUser['id'] ?? null)
            : ($authUser['linked_admin_id'] ?? null);

        if (!$adminId) {
            return null;
        }

        $admin = DB::table('admins')
            ->select('id', 'full_name', 'ic_no', 'role')
            ->where('id', $adminId)
            ->where('role', 'system_admin')
            ->first();

        if (!$admin || (($authUser['role'] ?? null) === 'admin' && ($authUser['admin_role'] ?? null) !== $admin->role)) {
            return null;
        }

        return $admin;
    }

    public static function linkedStudent(object $admin): ?object
    {
        return DB::table('students')
            ->select('id', 'full_name')
            ->where('ic_no', $admin->ic_no)
            ->first();
    }

    public static function canSwitch(Request $request): bool
    {
        $admin = self::linkedAdmin($request);

        return $admin && self::linkedStudent($admin);
    }

    public static function switchToStudent(Request $request, bool $override): bool
    {
        $admin = self::linkedAdmin($request);
        $student = $admin ? self::linkedStudent($admin) : null;

        if (!$admin || !$student) {
            return false;
        }

        $request->session()->regenerate();
        $request->session()->put('auth_user', [
            'id' => (int) $student->id,
            'role' => 'student',
            'name' => $student->full_name,
            'linked_admin_id' => (int) $admin->id,
            'linked_admin_role' => $admin->role,
            'admin_override' => $override,
        ]);

        return true;
    }

    public static function switchToAdmin(Request $request): bool
    {
        $admin = self::linkedAdmin($request);
        if (!$admin) {
            return false;
        }

        $request->session()->regenerate();
        $request->session()->put('auth_user', [
            'id' => (int) $admin->id,
            'role' => 'admin',
            'name' => $admin->full_name,
            'admin_role' => $admin->role,
        ]);

        return true;
    }
}
