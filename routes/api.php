<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Exam\AttemptController as ExamAttemptController;
use App\Http\Controllers\Api\Exam\ExamController;
use App\Http\Controllers\Api\Exam\QuestionController;
use App\Http\Controllers\Api\Public\AnswerController;
use App\Http\Controllers\Api\Public\AttemptController;
use App\Http\Controllers\Api\Public\PublicExamController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('register/request-otp', 'requestOtp')->middleware('throttle:register-otp');
        Route::post('register/verify-otp', 'verifyOtp')->middleware('throttle:verify-otp');
        Route::post('login', 'login')->middleware('throttle:login');
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', 'me');
            Route::post('logout', 'logout');
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('exams', ExamController::class);
        Route::apiResource('exams.questions', QuestionController::class);

        Route::prefix('exams/{exam}/attempts')->controller(ExamAttemptController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('{attempt}', 'show')->scopeBindings();
            Route::get('{attempt}/answers/{answer}/download', 'downloadAnswerFile')
                ->scopeBindings()
                ->name('exams.attempts.answers.download');
        });
    });

    Route::prefix('public')->group(function () {
        Route::prefix('exams/{slug}')->group(function () {
            Route::get('/', [PublicExamController::class, 'show']);

            Route::prefix('attempts')
                ->controller(AttemptController::class)->group(function () {
                    Route::post('request-otp', 'requestOtp')->middleware('throttle:attempt-request-otp');
                    Route::post('verify-otp', 'verifyOtp')->middleware('throttle:attempt-verify-otp');
                    Route::get('{attempt}/resume', 'resume')->middleware('throttle:attempt-answers');
                });

            Route::prefix('attempts/{attempt}')
                ->controller(AnswerController::class)
                ->middleware('throttle:attempt-answers')
                ->group(function () {
                    Route::post('questions/{question}/answer', 'store');
                    Route::post('complete', 'complete');
                });
        });
    });
});
