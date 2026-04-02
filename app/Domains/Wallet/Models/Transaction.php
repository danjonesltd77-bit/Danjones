<?php

namespace App\Domains\Wallet\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'wallet_type',
        'currency_id',
        'action',
        'amount',
        'usd',
        'type',
        'previous_balance',
        'current_balance',
        'reference',
        'description',
        'metadata',
        'status',
    ];

    protected $casts = [
        'metadata' => 'array',
        'type' => \App\Enum\TransactionType::class,
        'action' => \App\Enum\TransactionAction::class,
        'status' => \App\Enum\TransactionStatus::class,
    ];

    public function wallet()
    {
        return $this->morphTo('wallet', 'wallet_type', 'wallet_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
