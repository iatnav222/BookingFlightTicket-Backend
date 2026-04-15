<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckAdmin; // <-- Nhớ thêm dòng use này ở trên cùng

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // Ngoại trừ các route này khỏi kiểm tra CSRF
        $middleware->validateCsrfTokens(except: [
            'users',
            'users/*',
        ]);

        // ĐĂNG KÝ MIDDLEWARE CỦA BẠN Ở ĐÂY
        $middleware->alias([
            'isAdmin' => CheckAdmin::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();