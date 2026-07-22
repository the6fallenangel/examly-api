<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::middleware('guest.api')->group(function () {
            Route::post('register/request-otp', 'requestOtp')->middleware('throttle:register-otp');
            Route::post('register/verify-otp', 'verifyOtp')->middleware('throttle:verify-otp');
            Route::post('login', 'login');
        });
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', 'me');
            Route::post('logout', 'logout');
        });
    });
});
