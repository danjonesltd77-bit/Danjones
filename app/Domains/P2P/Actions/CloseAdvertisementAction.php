<?php

namespace App\Domains\P2P\Actions;

use App\Domains\P2P\Models\P2PAdvertisement;
use App\Enum\TradeStatus;
use App\Models\User;
use Exception;

class CloseAdvertisementAction
{
    /**
     * Terminate an active P2P advertisement.
     *
     * @throws Exception
     */
    public function execute(User $user, P2PAdvertisement $ad): P2PAdvertisement
    {
        if ($ad->user_id !== $user->id) {
            throw new Exception('You are not authorized to close this advertisement.', 403);
        }

        if (! $ad->is_active) {
            throw new Exception('This advertisement is already closed.', 400);
        }

        $activeTradeCount = $ad->trades()
            ->whereIn('status', [TradeStatus::PENDING, TradeStatus::PAID, TradeStatus::DISPUTED])
            ->count();

        if ($activeTradeCount > 0) {
            throw new Exception("Cannot close advertisement with {$activeTradeCount} active trade(s).", 400);
        }

        $ad->update(['is_active' => false]);

        return $ad;
    }
}
