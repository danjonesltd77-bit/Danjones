<?php

namespace App\Domains\User\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterUserAction
{
    public function execute(array $data): User
    {
        $ref_code = Str::random(4);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'ref_code' => $ref_code,
            'phone' => $data['phone'],
        ]);

        return $user;
    }
}
