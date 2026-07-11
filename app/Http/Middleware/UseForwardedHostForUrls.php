<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class UseForwardedHostForUrls
{
    public function handle(Request $request, Closure $next): Response
    {
        $forwardedHost = trim((string) $request->headers->get('x-forwarded-host', ''));
        $forwardedProto = trim((string) $request->headers->get('x-forwarded-proto', ''));

        $host = $forwardedHost !== '' ? $forwardedHost : $request->getHttpHost();
        $scheme = $forwardedProto !== '' ? $forwardedProto : $request->getScheme();

        if ($host !== '') {
            URL::forceRootUrl($scheme.'://'.$host);
        }

        if (in_array($scheme, ['http', 'https'], true)) {
            URL::forceScheme($scheme);
        }

        return $next($request);
    }
}
