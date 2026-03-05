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
    ->withMiddleware(function (Middleware $middleware) {
        // Remove problematic middleware for Laravel 12
        $middleware->remove([
            \Illuminate\Foundation\Http\Middleware\ValidatePathEncoding::class,
        ]);
        
        // Add web middleware group if needed
        $middleware->web(append: [
            // your web middleware
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();