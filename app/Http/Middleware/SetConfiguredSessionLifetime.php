<?php

namespace App\Http\Middleware;

use App\Support\SystemSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetConfiguredSessionLifetime
{
    public function __construct(private readonly SystemSettings $settings) {}

    public function handle(Request $request, Closure $next): Response
    {
        config(['session.lifetime' => $this->settings->sessionLifetime()]);

        return $next($request);
    }
}
