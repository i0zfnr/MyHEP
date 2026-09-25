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

        $connectSources = ["'self'", 'https://cloudflareinsights.com'];
        $scriptSources = ["'self'", "'unsafe-inline'", 'https://static.cloudflareinsights.com'];
        $styleSources = ["'self'", "'unsafe-inline'", 'https://fonts.googleapis.com'];

        if (app()->environment('local')) {
            array_push(
                $connectSources,
                'http://localhost:5173', 'http://127.0.0.1:5173',
                'ws://localhost:5173', 'ws://127.0.0.1:5173'
            );
            array_push($scriptSources, 'http://localhost:5173', 'http://127.0.0.1:5173');
            array_push($styleSources, 'http://localhost:5173', 'http://127.0.0.1:5173');
        }

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            'connect-src '.implode(' ', $connectSources),
            "font-src 'self' https://fonts.gstatic.com data:",
            "form-action 'self'",
            "frame-ancestors 'self' https://portfolio.ryz.my.id http://localhost:* http://127.0.0.1:*",
            "frame-src 'self' https://maps.google.com https://www.google.com",
            "img-src 'self' data: blob: https:",
            "object-src 'none'",
            'script-src '.implode(' ', $scriptSources),
            'style-src '.implode(' ', $styleSources),
        ]));
        $response->headers->set('Permissions-Policy', 'camera=(self), geolocation=(self), microphone=()');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->remove('X-Frame-Options');

        if ($request->isSecure() && app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
