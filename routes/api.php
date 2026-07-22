<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register/request-otp', [AuthController::class, 'requestOtp'])->middleware('throttle:register-otp');
        Route::post('register/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:verify-otp');
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
        });
    });
});
