<?php

namespace App\Enum;

enum TradeStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case DISPUTED = 'disputed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::DISPUTED => 'Disputed',
        };
    }
}
