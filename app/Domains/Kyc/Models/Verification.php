<?php

namespace App\Domains\Kyc\Models;

use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    protected $guarded = [];

    public function userVerifications()
    {
        return $this->hasMany(UserVerification::class);
    }
}
