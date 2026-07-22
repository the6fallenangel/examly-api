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
        $limiter = function (Request $request, string $period, bool $haveEmail = true, int $allowed = 5) {
            $key = $request->ip();
            if ($haveEmail) {
                $email = strtolower(trim($request->input('email', '')));
                $key .= '|'.$email;
            }

            return Limit::{$period}(
                app()->environment('local') ? 100 : $allowed
            )->by(
                $key
            );
        };

        RateLimiter::for('register-otp', fn (Request $request) => $limiter($request, 'perHour'));
        RateLimiter::for('verify-otp', fn (Request $request) => $limiter($request, 'perMinute'));

        RateLimiter::for('login', fn (Request $request) => $limiter($request, 'perMinute'));

        RateLimiter::for('attempt-request-otp', fn (Request $request) => $limiter($request, 'perHour'));
        RateLimiter::for('attempt-verify-otp', fn (Request $request) => $limiter($request, 'perMinute'));

        RateLimiter::for('attempt-answers', fn (Request $request) => $limiter($request, 'perMinute', false, 30));
    }
}
