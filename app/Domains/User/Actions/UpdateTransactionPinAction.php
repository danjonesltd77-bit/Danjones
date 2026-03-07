<?php

namespace App\Domains\User\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UpdateTransactionPinAction
{
    /**
     * Verify the current PIN and update to a new one.
     *
     * @throws ValidationException
     */
    public function execute(User $user, string $currentPassword, string $newPin): User
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->pin = Hash::make($newPin);
        $user->save();

        return $user;
    }
}
