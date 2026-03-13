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
    public function execute(?User $user, P2PTrade $trade): P2PTrade
    {
        if ($trade->status !== TradeStatus::PENDING && $trade->status !== TradeStatus::PAID) {
            throw new Exception('Only pending or paid trades can be cancelled.', 400);
        }

        if ($user && $trade->buyer_id !== $user->id && $trade->seller_id !== $user->id) {
            throw new Exception('Unauthorized to cancel this trade.', 403);
        }

        $escrowWallet = SystemWallet::where('currency_id', $trade->currency_id)
            ->where('type', SystemWalletType::ESCROW)
            ->first();

        if (! $escrowWallet) {
            throw new Exception('System escrow wallet not found.', 500);
        }

        return DB::transaction(function () use ($trade, $escrowWallet) {
            $trade->status = TradeStatus::CANCELLED;
            $trade->save();

            // Return available amount to advertisement
            $ad = $trade->advertisement;
            $ad->available_amount += $trade->crypto_amount;
            $ad->save();

            $seller = $trade->seller;
            $sellerWallet = $seller->wallet($trade->currency_id);

            // Refund from escrow to seller
            $reference = 'trade_cancel_'.$trade->id;
            $this->ledgerService->recordDeposit(
                systemWallet: $escrowWallet,
                userWallet: $sellerWallet,
                amount: $trade->crypto_amount,
                usdAmount: 0,
                reference: $reference,
                description: 'P2P Trade Cancelled Refund'
            );

            return $trade;
        });
    }
}
