<?php

namespace App\Enum;

enum SystemWalletType: string
{
    case HOT = 'hot';
    case COLD = 'cold';
    case FEE = 'fee';
    case REVENUE = 'revenue';
    case GAS = 'gas';

    public function label(): string
    {
        return match ($this) {
            self::HOT => 'Hot Wallet',
            self::COLD => 'Cold Wallet',
            self::FEE => 'Fee Wallet',
            self::REVENUE => 'Revenue Wallet',
            self::GAS => 'Gas Wallet',
        };
    }
}
