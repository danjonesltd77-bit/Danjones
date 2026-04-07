<?php

namespace App\Domains\User\Actions;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class VerifyOtpAction
{
    public function execute(User $user, string $otp): bool
    {
        if ($user->otp !== $otp) {
            throw ValidationException::withMessages([
                'otp' => ['The provided OTP is incorrect.'],
            ]);
        }

        if ($user->otp_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'otp' => ['The OTP has expired.'],
            ]);
        }

        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
            'email_verified_at' => now(),
        ]);

        return true;
    }
}
