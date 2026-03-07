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
    public function execute(User $user, string $password, string $newPin): User
    {
        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The current password is incorrect.'],
            ]);
        }

        $user->pin = Hash::make($newPin);
        $user->save();

        return $user;
    }
}
