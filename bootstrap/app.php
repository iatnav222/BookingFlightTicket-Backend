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
        
        // Ngoại trừ các route này khỏi kiểm tra CSRF
        $middleware->validateCsrfTokens(except: [
            'users',
            'users/*',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();