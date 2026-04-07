<?php

namespace App\Domains\User\Actions;

use App\Domains\User\Notifications\SendOtpNotification;
use App\Models\User;

class GenerateOtpAction
{
    public function execute(User $user): void
    {
        $otp = (string) rand(100000, 999999);

        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $user->notify(new SendOtpNotification($otp));
    }
}
