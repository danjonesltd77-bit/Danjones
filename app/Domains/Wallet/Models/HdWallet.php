<?php

namespace App\Domains\Wallet\Models;

use Illuminate\Database\Eloquent\Model;

class HdWallet extends Model
{
    protected $guarded = [];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
