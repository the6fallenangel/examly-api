<?php

namespace App\Actions\Attempt;

use App\Models\Attempt;
use App\Models\Exam;
use App\Services\OtpService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VerifyAttemptOtpAction
{
    public function execute(Exam $exam, string $email, string $otp, string $name, ?string $ip): Attempt
    {
        $email = strtolower(trim($email));

        $otpService = $this->otpService($exam);

        if (! $otpService->verify($email, $otp)) {
            throw ValidationException::withMessages([
                'otp' => 'invalid verification code',
            ]);
        }

        $otpService->forget($email);

        $existing = Attempt::query()
            ->where('exam_id', $exam->id)
            ->where('taker_email', $email)
            ->first();

        if ($existing?->completed_at !== null) {
            throw ValidationException::withMessages([
                'email' => 'you have already completed this exam',
            ]);
        }

        return Attempt::query()->updateOrCreate(
            ['exam_id' => $exam->id, 'taker_email' => $email],
            [
                'taker_name' => $name,
                'verified_at' => now(),
                'started_at' => $existing?->started_at ?? now(),
                'ip_address' => $ip,
                'token' => $existing?->token ?? $this->generateToken(),
            ]
        );
    }

    private function generateToken()
    {
        do {
            $token = Str::random(64);
        } while (Attempt::query()->where('token', $token)->exists());

        return $token;
    }

    private function otpService(Exam $exam): OtpService
    {
        return new OtpService("attempt:{$exam->id}");
    }
}
