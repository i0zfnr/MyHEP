<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureAdminScope;
use App\Http\Middleware\RequireSessionAuthenticated;
use App\Http\Middleware\RequireSessionRole;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TranslateFrontendContent;
use App\Http\Middleware\UseForwardedHostForUrls;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            UseForwardedHostForUrls::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
            TranslateFrontendContent::class,
        ]);

        $middleware->alias([
            'auth.session' => RequireSessionRole::class,
            'auth.session.any' => RequireSessionAuthenticated::class,
            'admin.scope' => EnsureAdminScope::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
