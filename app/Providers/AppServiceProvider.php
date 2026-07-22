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
        $otpLimiter = function (Request $request, string $period) {
            $email = strtolower(trim($request->input('email', '')));

            return Limit::{$period}(
                app()->environment('local') ? 100 : 5
            )->by(
                $request->ip().'|'.$email
            );
        };

        RateLimiter::for('register-otp', fn (Request $request) => $otpLimiter($request, 'perHour'));
        RateLimiter::for('verify-otp', fn (Request $request) => $otpLimiter($request, 'perMinute'));

        RateLimiter::for('login', fn (Request $request) => $otpLimiter($request, 'perMinute'));

        RateLimiter::for('attempt-request-otp', fn (Request $request) => $otpLimiter($request, 'perHour'));
        RateLimiter::for('attempt-verify-otp', fn (Request $request) => $otpLimiter($request, 'perMinute'));
    }
}
