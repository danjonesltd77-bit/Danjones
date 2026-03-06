<?php

namespace App\Domains\Wallet\Models;

use Illuminate\Database\Eloquent\Model;

class SystemWallet extends Model
{
    protected $fillable = [
        'type',
        'currency_id',
        'address',
        'balance',
    ];

    protected $casts = [
        'type' => \App\Enum\SystemWalletType::class,
    ];
}
