<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "connect-src 'self' https://cloudflareinsights.com",
            "font-src 'self' https://fonts.gstatic.com data:",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "frame-src 'self' https://maps.google.com https://www.google.com",
            "img-src 'self' data: blob: https:",
            "object-src 'none'",
            "script-src 'self' 'unsafe-inline' https://static.cloudflareinsights.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
        ]));
        $response->headers->set('Permissions-Policy', 'camera=(self), geolocation=(self), microphone=()');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');

        if ($request->isSecure() && app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
