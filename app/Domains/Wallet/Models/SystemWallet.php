<?php

namespace App\Domains\Wallet\Models;

use App\Domains\Wallet\Contracts\WalletAccountInterface;
use App\Enum\SystemWalletType;
use Illuminate\Database\Eloquent\Model;

class SystemWallet extends Model implements WalletAccountInterface
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\Domains\Wallet\Models\SystemWalletFactory::new();
    }

    protected $fillable = [
        'type',
        'currency_id',
        'address',
        'balance',
    ];

    protected $casts = [
        'type' => SystemWalletType::class,
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function getWalletId(): string
    {
        return $this->id;
    }

    public function getCurrencyId(): int
    {
        return $this->currency_id;
    }

    public function getWalletType(): string
    {
        return self::class;
    }

    public function getUserId(): int
    {
        return 0; // System wallets do not belong to users
    }

    public function getLedgerBalance(): float
    {
        return $this->balance;
    }
}
