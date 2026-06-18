<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Behind Railway's TLS-terminating proxy: trust forwarded headers so
        // generated URLs/redirects use https instead of http.
        $middleware->trustProxies(at: '*');

        // Static-key guard for the external integration API (/api/v1/*).
        $middleware->alias([
            'api.key' => \App\Http\Middleware\ApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
