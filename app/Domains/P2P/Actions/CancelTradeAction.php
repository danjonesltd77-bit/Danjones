<?php

namespace App\Domains\P2P\Actions;

use App\Domains\P2P\Models\P2PTrade;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Services\LedgerService;
use App\Enum\SystemWalletType;
use App\Enum\TradeStatus;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class CancelTradeAction
{
    public function __construct(protected LedgerService $ledgerService) {}

    /**
     * @throws Exception
     */
    public function execute(?User $user, P2PTrade $trade, bool $isAdmin = false): P2PTrade
    {
        if (! $isAdmin && $trade->status !== TradeStatus::PENDING && $trade->status !== TradeStatus::PAID) {
            throw new Exception('Only pending or paid trades can be cancelled.', 400);
        }

        if ($isAdmin && $trade->status !== TradeStatus::PENDING && $trade->status !== TradeStatus::PAID && $trade->status !== TradeStatus::DISPUTED) {
            throw new Exception('Admin can only cancel pending, paid or disputed trades.', 400);
        }

        if (! $isAdmin && $user && $trade->buyer_id !== $user->id && $trade->seller_id !== $user->id) {
            throw new Exception('Unauthorized to cancel this trade.', 403);
        }

        $escrowWallet = SystemWallet::where('currency_id', $trade->currency_id)
            ->where('type', SystemWalletType::ESCROW)
            ->first();

        if (! $escrowWallet) {
            throw new Exception('System escrow wallet not found.', 500);
        }

        return DB::transaction(function () use ($trade, $escrowWallet) {
            // Re-fetch and lock the trade to ensure it hasn't already been processed
            $lockedTrade = P2PTrade::where('id', $trade->id)->lockForUpdate()->firstOrFail();

            if ($lockedTrade->status === TradeStatus::CANCELLED) {
                return $lockedTrade;
            }

            $lockedTrade->status = TradeStatus::CANCELLED;
            $lockedTrade->save();

            // Return available amount to advertisement
            $ad = $lockedTrade->advertisement;
            $lockedAd = \App\Domains\P2P\Models\P2PAdvertisement::where('id', $ad->id)->lockForUpdate()->firstOrFail();
            $lockedAd->available_amount += $lockedTrade->crypto_amount;
            $lockedAd->save();

            $seller = $lockedTrade->seller;
            $sellerWallet = $seller->wallet($lockedTrade->currency_id);
            $lockedSellerWallet = \App\Domains\Wallet\Models\Wallet::where('id', $sellerWallet->id)->lockForUpdate()->firstOrFail();

            // Refund from escrow to seller
            $reference = 'trade_cancel_'.$lockedTrade->id;
            $this->ledgerService->recordDeposit(
                systemWallet: $escrowWallet,
                userWallet: $lockedSellerWallet,
                amount: $lockedTrade->crypto_amount,
                usdAmount: 0,
                reference: $reference,
                description: 'P2P Trade Cancelled Refund'
            );

            return $lockedTrade;
        });
    }
}
