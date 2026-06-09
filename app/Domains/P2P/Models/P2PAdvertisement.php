<?php

namespace App\Domains\P2P\Models;

use App\Domains\Wallet\Models\Currency;
use App\Enum\AdvertisementType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P2PAdvertisement extends Model
{
    /** @use HasFactory<\Database\Factories\Domains\P2P\Models\P2PAdvertisementFactory> */
    use HasFactory;

    protected $table = 'p2p_advertisements';

    protected $fillable = [
        'user_id',
        'currency_id',
        'bank_account_id',
        'type',
        'price',
        'total_amount',
        'available_amount',
        'min_limit',
        'max_limit',
        'terms',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => AdvertisementType::class,
            'is_active' => 'boolean',
            'price' => 'float',
            'total_amount' => 'float',
            'available_amount' => 'float',
            'min_limit' => 'float',
            'max_limit' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(P2PTrade::class, 'advertisement_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Bank\Models\BankAccount::class);
    }
}
