<?php

namespace App\Domains\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Wallet\Contracts\WalletAccountInterface;

class SystemWallet extends Model implements WalletAccountInterface
{
    protected $fillable = [
        'type',
        'currency_id',
        'address',
        'balance',
    ];

    protected $casts = [
        'type' => \App\Enum\SystemWalletType::class,
    ];

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function getWalletId(): string
    {
        return $this->id;
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
