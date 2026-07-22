<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\LogoutAction;
use App\Actions\Auth\SendRegisterOtpAction;
use App\Actions\Auth\VerifyRegisterOtpAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\SendRegisterOtpRequest;
use App\Http\Requests\Auth\VerifyRegisterOtpRequest;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function requestOtp(
        SendRegisterOtpRequest $req,
        SendRegisterOtpAction $act
    ): JsonResponse {
        $act->execute($req->validated('email'));

        return ApiResponse::success(message: 'verification code sent successfully');
    }

    public function verifyOtp(
        VerifyRegisterOtpRequest $req,
        VerifyRegisterOtpAction $act
    ): JsonResponse {
        [$user, $token] = $act->execute(
            email: $req->validated('email'),
            otp: $req->validated('otp'),
            name: $req->validated('name'),
            password: $req->validated('password'),
        );

        return ApiResponse::success(
            message: 'account created successfully',
            data: [
                'user' => new UserResource($user),
                'token' => $token,
            ],
            statusCode: Response::HTTP_CREATED
        );
    }

    public function me(): JsonResponse
    {
        return ApiResponse::success(
            data: new UserResource(request()->user())
        );
    }

    public function login(
        LoginRequest $req,
        LoginAction $act
    ): JsonResponse {
        [$user, $token] = $act->execute(
            email: $req->validated('email'),
            password: $req->validated('password')
        );

        return ApiResponse::success(
            message: 'logged in successfully',
            data: [
                'user' => new UserResource($user),
                'token' => $token,
            ]
        );
    }

    public function logout(
        Request $req,
        LogoutAction $act
    ): JsonResponse {
        $act->execute($req);

        return ApiResponse::success(message: 'logged out successfully');
    }
}
