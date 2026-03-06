<?php

namespace App\Enum;

enum TransactionAction: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAWAL = 'withdrawal';
    case TRANSFER = 'transfer';
    case FEE = 'fee';
    case REFUND = 'refund';
    case SELL = 'sell';
    case BUY = 'buy';

    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT => 'Deposit',
            self::WITHDRAWAL => 'Withdrawal',
            self::TRANSFER => 'Transfer',
            self::FEE => 'Fee',
            self::REFUND => 'Refund',
            self::SELL => 'Sell',
            self::BUY => 'Buy',
        };
    }
}
