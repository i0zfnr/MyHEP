<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        $student = $request->user();

        if (! $student instanceof Student || ! $student->currentAccessToken()
            || ! $student->currentAccessToken()->can('student:read')
            || (bool) ($student->is_blacklisted ?? false)) {
            return response()->json(['message' => 'This student account cannot access the mobile API.'], 403);
        }

        return $next($request);
    }
}
