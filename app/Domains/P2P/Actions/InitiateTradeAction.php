<?php

namespace App\Domains\P2P\Actions;

use App\Domains\P2P\Models\P2PAdvertisement;
use App\Domains\P2P\Models\P2PTrade;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Services\LedgerService;
use App\Enum\AdvertisementType;
use App\Enum\SystemWalletType;
use App\Enum\TradeStatus;
use App\Mail\P2P\TradeInitiatedMail;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InitiateTradeAction
{
    public function __construct(protected LedgerService $ledgerService) {}

    /**
     * @throws Exception
     */
    public function execute(User $initiator, P2PAdvertisement $ad, float $fiatAmount): P2PTrade
    {
        if (! $ad->is_active) {
            throw new Exception('This advertisement is no longer active.', 400);
        }

        if ($initiator->id === $ad->user_id) {
            throw new Exception('You cannot trade with your own advertisement.', 400);
        }

        if ($fiatAmount < $ad->min_limit || $fiatAmount > $ad->max_limit) {
            throw new Exception("Amount is out of the specified limits (Min: {$ad->min_limit}, Max: {$ad->max_limit}).", 400);
        }

        $cryptoAmount = $fiatAmount / $ad->price;

        if ($cryptoAmount > $ad->available_amount) {
            throw new Exception('Insufficient crypto available in this advertisement.', 400);
        }

        // Restrict buyer from creating two trades at the same time for the same ads when one is pending/paid
        $isAdCreatorSelling = $ad->type === AdvertisementType::SELL;
        $buyerId = $isAdCreatorSelling ? $initiator->id : $ad->user_id;

        $existingTrade = P2PTrade::where('advertisement_id', $ad->id)
            ->where('buyer_id', $buyerId)
            ->whereIn('status', [TradeStatus::PENDING, TradeStatus::PAID])
            ->exists();

        if ($existingTrade) {
            throw new Exception('You already have an active trade for this advertisement. Please complete or cancel it before opening a new one.', 400);
        }

        $isAdCreatorSelling = $ad->type === AdvertisementType::SELL;
        $seller = $isAdCreatorSelling ? $ad->user : $initiator;
        $buyer = $isAdCreatorSelling ? $initiator : $ad->user;

        $sellerWallet = $seller->wallet($ad->currency_id);
        if (! $sellerWallet || $sellerWallet->balance < $cryptoAmount) {
            throw new Exception('Seller does not have enough crypto balance.', 400);
        }

        $escrowWallet = SystemWallet::where('currency_id', $ad->currency_id)
            ->where('type', SystemWalletType::ESCROW)
            ->first();

        if (! $escrowWallet) {
            throw new Exception('System escrow wallet not found.', 500);
        }

        return DB::transaction(function () use ($ad, $seller, $buyer, $sellerWallet, $escrowWallet, $cryptoAmount, $fiatAmount) {
            // Re-fetch and lock the advertisement to prevent race conditions on available_amount
            $lockedAd = P2PAdvertisement::where('id', $ad->id)->lockForUpdate()->firstOrFail();

            // Re-fetch and lock the seller's wallet
            $lockedSellerWallet = \App\Domains\Wallet\Models\Wallet::where('id', $sellerWallet->id)->lockForUpdate()->firstOrFail();

            if ($cryptoAmount > $lockedAd->available_amount) {
                throw new Exception('Insufficient crypto available in this advertisement during processing.', 400);
            }

            if ($lockedSellerWallet->balance < $cryptoAmount) {
                throw new Exception('Seller does not have enough crypto balance during processing.', 400);
            }

            // Deduct available amount from ad immediately
            $lockedAd->available_amount -= $cryptoAmount;
            $lockedAd->save();

            // Create Trade
            $trade = P2PTrade::create([
                'advertisement_id' => $lockedAd->id,
                'seller_id' => $seller->id,
                'buyer_id' => $buyer->id,
                'currency_id' => $lockedAd->currency_id,
                'crypto_amount' => $cryptoAmount,
                'fiat_amount' => $fiatAmount,
                'status' => TradeStatus::PENDING,
            ]);

            $reference = 'trade_'.$trade->id;
            $this->ledgerService->recordWithdrawal(
                userWallet: $lockedSellerWallet,
                systemWallet: $escrowWallet,
                amount: $cryptoAmount,
                usdAmount: 0,
                reference: $reference,
                description: 'P2P Trade Escrow Lock'
            );

            Mail::to($trade->seller->email)->queue(new TradeInitiatedMail($trade));
            
            return $trade;
        });
    }
}
