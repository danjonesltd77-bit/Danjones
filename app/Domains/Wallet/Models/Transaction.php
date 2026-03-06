<?php

namespace App\Domains\Wallet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'currency_id',
        'action',
        'amount',
        'usd',
        'fee',
        'fee_usd',
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
}
