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

    protected $fillable = [
        'parent_id',
        'name',
        'image',
        'symbol',
        'coingecko_id',
        'decimal',
        'fee',
        'is_crypto',
        'is_active',
        'is_gaspump',
        'token_currency',
        'token_id',
        'token_address',
        'contract_type',
        'change_address',
        'price_change_24h',
    ];

    protected $casts = [
        'price_change_24h' => 'float',
        'is_crypto' => 'boolean',
        'is_active' => 'boolean',
        'is_gaspump' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Currency::class, 'parent_id');
    }

    public function hdWallet()
    {
        return $this->hasOne(HdWallet::class);
    }
}
