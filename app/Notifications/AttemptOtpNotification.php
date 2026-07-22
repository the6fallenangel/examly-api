<?php

namespace App\Notifications;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttemptOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $otp,
        private readonly Exam $exam,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your verification code for {$this->exam->title}")
            ->line('Your verification code is:')
            ->line($this->otp)
            ->line('This code expires in 5 minutes.')
            ->line('If you did not request this, you can ignore this email.');
    }
}
