<?php

namespace App\Domains\Kyc\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserVerification extends Model
{
    protected $fillable = [
        'user_id',
        'verification_id',
        'status',
        'data',
        'reference',
        'reason',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verification()
    {
        return $this->belongsTo(Verification::class);
    }
}
