<?php

namespace App\Domains\User\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SetTransactionPinAction
{
    /**
     * Execute the action.
     *
     * @param User $user
     * @param string $pin
     * @return User
     */
    public function execute(User $user, string $pin): User
    {
        $user->pin = Hash::make($pin);
        $user->save();

        return $user;
    }
}
