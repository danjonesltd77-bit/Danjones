<?php

namespace App\Domains\Wallet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\Domains\Wallet\Models\CurrencyFactory::new();
    }

    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(Currency::class, 'parent_id');
    }

    public function hdWallet()
    {
        return $this->hasOne(HdWallet::class);
    }
}
