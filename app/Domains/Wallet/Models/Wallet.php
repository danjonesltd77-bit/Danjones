<?php

namespace App\Domains\Wallet\Models;

use App\Domains\Wallet\Contracts\WalletAccountInterface;
use App\Enum\WalletStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model implements WalletAccountInterface
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'currency_id',
        'address',
        'balance',
        'index',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => WalletStatus::class,
        ];
    }

    public function getWalletId(): string
    {
        return (string) $this->id;
    }

    public function getWalletType(): string
    {
        return self::class;
    }

    public function getUserId(): int
    {
        return (int) $this->user_id;
    }

    public function getCurrencyId(): int
    {
        return (int) $this->currency_id;
    }

    public function getLedgerBalance(): float
    {
        return (float) $this->balance;
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'wallet');
    }
}
