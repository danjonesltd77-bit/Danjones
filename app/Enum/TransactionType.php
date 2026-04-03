<?php

namespace App\Enum;

enum TransactionType: string
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';

    public function label(): string
    {
        return match ($this) {
            self::DEBIT => 'Debit',
            self::CREDIT => 'Credit',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DEBIT => 'bg-danger/10 text-danger',
            self::CREDIT => 'bg-success/10 text-success',
        };
    }

    public function isCredit(): bool
    {
        return $this === self::CREDIT;
    }

    public function isDebit(): bool
    {
        return $this === self::DEBIT;
    }
}
