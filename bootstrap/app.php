<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register Spatie Permission middleware
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'rate.limit.forms' => \App\Http\Middleware\RateLimitForms::class,
            'block.site' => \App\Http\Middleware\BlockSiteMiddleware::class,
        ]);

        // Apply block site middleware to all web routes
        $middleware->web(\App\Http\Middleware\BlockSiteMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
