<?php

namespace App\Domains\P2P\Actions;

use App\Domains\P2P\Models\P2PTrade;
use App\Enum\TradeStatus;
use App\Models\User;
use Exception;

class MarkTradePaidAction
{
    /**
     * @throws Exception
     */
    public function execute(User $user, P2PTrade $trade): P2PTrade
    {
        if ($trade->status !== TradeStatus::PENDING) {
            throw new Exception('Trade is not in a pending state.', 400);
        }

        if ($trade->buyer_id !== $user->id) {
            throw new Exception('Only the buyer can mark the trade as paid.', 403);
        }

        $trade->status = TradeStatus::PAID;
        $trade->save();

        return $trade;
    }
}
