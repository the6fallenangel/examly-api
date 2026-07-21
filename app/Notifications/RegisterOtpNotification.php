<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisterOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $otp) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Examly verification code')
            ->line('Your verification code is:')
            ->line($this->otp)
            ->line('This code expires in 5 minutes.')
            ->line('If you did not request this, you can ignore this email.');
    }
}
