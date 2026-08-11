<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureAdminScope;
use App\Http\Middleware\EnsureFeatureEnabled;
use App\Http\Middleware\EnsureLecturerPageAccess;
use App\Http\Middleware\RequireSessionAuthenticated;
use App\Http\Middleware\RequireSessionRole;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SetConfiguredSessionLifetime;
use App\Http\Middleware\TrackAccountSession;
use App\Http\Middleware\TranslateFrontendContent;
use App\Http\Middleware\SecurityHeaders;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustHosts(
            at: fn (): array => config('security.allowed_hosts'),
            subdomains: false,
        );

        $middleware->web(prepend: [
            SetConfiguredSessionLifetime::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
            TrackAccountSession::class,
            TranslateFrontendContent::class,
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'auth.session' => RequireSessionRole::class,
            'auth.session.any' => RequireSessionAuthenticated::class,
            'admin.scope' => EnsureAdminScope::class,
            'feature.enabled' => EnsureFeatureEnabled::class,
            'lecturer.page' => EnsureLecturerPageAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
