<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        RateLimiter::for('register-otp', function (Request $request) {
            $email = strtolower(trim($request->input('email', '')));

            $perHour = app()->environment('local') ? 100 : 5;

            return Limit::perHour($perHour)
                ->by($request->ip().'|'.$email);
        });
    }
}
