<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VerifyRegisterOtpAction
{
    public function __construct(private readonly OtpService $otpService) {}

    public function execute(
        string $email,
        string $otp,
        string $name,
        string $password
    ): array {
        if (! $this->otpService->verify($email, $otp)) {
            throw ValidationException::withMessages([
                'otp' => 'invalid verification code',
            ]);
        }

        $user = DB::transaction(function () use ($name, $email, $password) {
            return User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'email_verified_at' => now(),
            ]);
        });
        $this->otpService->forget($email);

        $token = '';
        if ($user instanceof User) {
            $token = $user->createToken('web')->plainTextToken;
        }

        return [$user, $token];
    }
}
