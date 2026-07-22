<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\SendRegisterOtpAction;
use App\Actions\Auth\VerifyRegisterOtpAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendRegisterOtpRequest;
use App\Http\Requests\Auth\VerifyRegisterOtpRequest;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function requestOtp(
        SendRegisterOtpRequest $req,
        SendRegisterOtpAction $act
    ): JsonResponse {
        $act->execute($req->validated('email'));

        return ApiResponse::success(message: 'Verification code sent successfully');
    }

    public function verifyOtp(
        VerifyRegisterOtpRequest $req,
        VerifyRegisterOtpAction $act
    ): JsonResponse {
        $user = $act->execute(
            email: $req->validated('email'),
            otp: $req->validated('otp'),
            name: $req->validated('name'),
            password: $req->validated('password'),
        );

        Auth::login($user);

        return ApiResponse::success(
            message: 'account created successfully',
            data: new UserResource($user)
        );
    }
}
