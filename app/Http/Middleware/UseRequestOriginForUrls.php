<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class UseRequestOriginForUrls
{
    public function handle(Request $request, Closure $next): Response
    {
        $scheme = app()->environment('production') ? 'https' : $request->getScheme();
        $host = $request->getHttpHost();

        URL::forceRootUrl($scheme.'://'.$host);
        URL::forceScheme($scheme);

        return $next($request);
    }
}
