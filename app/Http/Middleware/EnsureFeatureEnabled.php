<?php

namespace App\Http\Middleware;

use App\Support\SystemFeatures;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    public function __construct(private readonly SystemFeatures $features) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if ($this->features->enabled($feature)) {
            return $next($request);
        }

        return response()->view('features.unavailable', [
            'feature' => SystemFeatures::FEATURES[$feature]['label'] ?? __('This feature'),
        ], 503);
    }
}
