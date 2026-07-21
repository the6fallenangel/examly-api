<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\SendRegisterOtpAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendRegisterOtpRequest;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function requestOtp(SendRegisterOtpRequest $req, SendRegisterOtpAction $act): JsonResponse
    {
        $act->execute($req->validated('email'));

        return ApiResponse::success(message: 'Verification code sent successfully');
    }
}
