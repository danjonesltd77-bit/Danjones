<?php

namespace App\Domains\Wallet\Models;

use Illuminate\Database\Eloquent\Model;

class HdWallet extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\Domains\Wallet\Models\HdWalletFactory::new();
    }

    protected $fillable = [
        'currency_id',
        'parent_id',
        'signature_id',
        'xpub',
        'private_key',
        'index',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
