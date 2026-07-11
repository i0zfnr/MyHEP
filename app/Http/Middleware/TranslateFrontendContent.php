<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TranslateFrontendContent
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Redirect and error responses must keep their original body and headers.
        if (! $response->isSuccessful() || app()->getLocale() !== 'en') {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if (!is_string($content) || $content === '') {
            return $response;
        }

        static $replace = null;
        if ($replace === null) {
            $replace = require lang_path('en/frontend_replace.php');
        }

        $response->setContent(strtr($content, $replace));

        return $response;
    }
}
