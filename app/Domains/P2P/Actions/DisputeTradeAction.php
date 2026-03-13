<?php

namespace App\Domains\P2P\Actions;

use App\Domains\P2P\Models\P2PTrade;
use App\Enum\TradeStatus;
use App\Models\User;
use Exception;

class DisputeTradeAction
{
    /**
     * Execute the action.
     *
     * @throws Exception
     */
    public function execute(User $user, P2PTrade $trade, string $reason): P2PTrade
    {
        // Only the buyer or seller can raise a dispute
        if ($trade->buyer_id !== $user->id && $trade->seller_id !== $user->id) {
            throw new Exception('You are not authorized to dispute this trade.', 403);
        }

        // Only pending or paid trades can be disputed
        if (! in_array($trade->status, [TradeStatus::PENDING, TradeStatus::PAID])) {
            throw new Exception('Only pending or paid trades can be disputed.', 400);
        }

        $trade->update([
            'status' => TradeStatus::DISPUTED,
            'disputed_by' => $user->id,
            'dispute_reason' => $reason,
        ]);

        return $trade;
    }
}
