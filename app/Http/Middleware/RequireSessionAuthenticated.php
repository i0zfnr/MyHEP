<?php

namespace App\Http\Middleware;

use App\Support\DualRoleSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RequireSessionAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = $request->session()->get('auth_user');
        if (!is_array($authUser) || !in_array($authUser['role'] ?? null, ['student', 'admin'], true)) {
            return redirect()->route('login');
        }

        $account = $authUser['role'] === 'admin'
            ? DB::table('admins')->select('id', 'role')->where('id', $authUser['id'] ?? 0)->first()
            : DB::table('students')->select('id')->where('id', $authUser['id'] ?? 0)->first();

        if (!$account || ($authUser['role'] === 'admin' && ($account->role ?? null) !== ($authUser['admin_role'] ?? null))) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        if (!empty($authUser['linked_admin_id']) && !DualRoleSession::linkedAdmin($request)) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        return $next($request);
    }
}
