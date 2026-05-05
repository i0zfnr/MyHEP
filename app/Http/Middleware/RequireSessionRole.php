<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSessionRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $authUser = $request->session()->get('auth_user');
        if (!$authUser || ($authUser['role'] ?? null) !== $role) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
