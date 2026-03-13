<?php

namespace App\Domains\P2P\Models;

use App\Domains\Wallet\Models\Currency;
use App\Enum\TradeStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class P2PTrade extends Model
{
    /** @use HasFactory<\Database\Factories\Domains\P2P\Models\P2PTradeFactory> */
    use HasFactory;

    protected $table = 'p2p_trades';

    protected $fillable = [
        'advertisement_id',
        'seller_id',
        'buyer_id',
        'currency_id',
        'crypto_amount',
        'fiat_amount',
        'status',
        'disputed_by',
        'dispute_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => TradeStatus::class,
            'crypto_amount' => 'float',
            'fiat_amount' => 'float',
        ];
    }

    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(P2PAdvertisement::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
