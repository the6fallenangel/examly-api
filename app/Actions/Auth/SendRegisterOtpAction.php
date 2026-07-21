<?php

namespace App\Actions\Auth;

use App\Notifications\RegisterOtpNotification;
use App\Services\OtpService;
use Illuminate\Support\Facades\Notification;

class SendRegisterOtpAction
{
    public function __construct(private readonly OtpService $otpService) {}

    public function execute(string $email): void
    {
        $otp = $this->otpService->generate($email);

        Notification::route('mail', $email)->notify(new RegisterOtpNotification($otp));
    }
}
