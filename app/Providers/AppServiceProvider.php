<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Nếu môi trường không phải là local (máy tính của bạn), thì ép dùng HTTPS
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }
    }
}
