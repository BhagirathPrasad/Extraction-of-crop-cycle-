<?php

use App\Http\Middleware\ActivityLogger;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register custom alias middleware
        $middleware->alias([
            'role'             => RoleMiddleware::class,
            'activity.logger'  => ActivityLogger::class,
        ]);

        // Apply activity logger to all authenticated web requests
        $middleware->web(append: [
            ActivityLogger::class,
        ]);

        // Set locale from user preferences on every request
        $middleware->web(prepend: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
