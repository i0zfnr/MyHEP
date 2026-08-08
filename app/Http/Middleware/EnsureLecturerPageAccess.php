<?php

namespace App\Http\Middleware;

use App\Support\LecturerPageAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLecturerPageAccess
{
    public function __construct(private readonly LecturerPageAccess $pages) {}

    public function handle(Request $request, Closure $next, string $page): Response
    {
        $authUser = $request->session()->get('auth_user');
        if (($authUser['admin_role'] ?? null) !== 'lecturer') {
            return $next($request);
        }

        if ($this->pages->enabled((int) ($authUser['id'] ?? 0), $page)) {
            return $next($request);
        }

        abort(403, __('This lecturer account is not allowed to access that page.'));
    }
}
