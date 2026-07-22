<?php

namespace App\Actions\Attempt;

use App\Models\Attempt;
use App\Models\Exam;
use App\Notifications\AttemptOtpNotification;
use App\Services\OtpService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class RequestAttemptOtpAction
{
    public function execute(Exam $exam, string $email): void
    {
        $email = strtolower(trim($email));

        $alreadyCompleted = Attempt::query()
            ->where('exam_id', $exam->id)
            ->where('taker_email', $email)
            ->whereNotNull('completed_at')
            ->exists();

        if ($alreadyCompleted) {
            throw ValidationException::withMessages([
                'email' => 'you have already completed this exam',
            ]);
        }

        $otp = $this->otpService($exam)->generate($email);

        Notification::route('mail', $email)->notify(
            new AttemptOtpNotification($otp, $exam)
        );
    }

    private function otpService(Exam $exam): OtpService
    {
        return new OtpService("attempt:{$exam->id}");
    }
}
