<?php

namespace App\Domains\User\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $otp) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Email Verification OTP')
            ->line('Your one-time password (OTP) for email verification is:')
            ->line($this->otp)
            ->line('This code will expire in 10 minutes.')
            ->line('If you did not request this, no further action is required.');
    }
}
