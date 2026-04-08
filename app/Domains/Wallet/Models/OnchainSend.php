<?php

namespace App\Domains\Wallet\Models;

use Illuminate\Database\Eloquent\Model;

class OnchainSend extends Model
{
    protected $guarded = [];

    protected $casts = [
        'sender_address' => 'array',
        'amount' => 'double',
        'fee' => 'double',
        'rate' => 'double',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
