<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::middleware('guest.api')->group(function () {
            Route::post('register/request-otp', [AuthController::class, 'requestOtp'])->middleware('throttle:register-otp');
            Route::post('register/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:verify-otp');
        });
    });
});
