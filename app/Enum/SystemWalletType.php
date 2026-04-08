<?php

namespace App\Enum;

enum SystemWalletType: string
{
    case HOT = 'hot';
    case COLD = 'cold';
    case FEE = 'fee';
    case SELL = 'sell';
    case GAS = 'gas';
    case DEPOSIT = 'deposit';
    case ESCROW = 'escrow';
    case CHANGE = 'change';

    public function label(): string
    {
        return match ($this) {
            self::HOT => 'Hot Wallet',
            self::COLD => 'Cold Wallet',
            self::FEE => 'Fee Wallet',
            self::SELL => 'Sell Wallet',
            self::GAS => 'Gas Wallet',
            self::DEPOSIT => 'Deposit Wallet',
            self::ESCROW => 'Escrow Wallet',
            self::CHANGE => 'Change Wallet',
        };
    }
}
