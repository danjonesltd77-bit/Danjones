<?php

namespace App\Domains\P2P\Actions;

use App\Domains\P2P\Models\P2PAdvertisement;
use App\Domains\P2P\Models\P2PTrade;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Services\LedgerService;
use App\Enum\AdvertisementType;
use App\Enum\SystemWalletType;
use App\Enum\TradeStatus;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

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
            // throw new Exception('You cannot trade with your own advertisement.', 400);
        }

        if ($fiatAmount < $ad->min_limit || $fiatAmount > $ad->max_limit) {
            throw new Exception("Amount is out of the specified limits (Min: {$ad->min_limit}, Max: {$ad->max_limit}).", 400);
        }

        $cryptoAmount = $fiatAmount / $ad->price;

        if ($cryptoAmount > $ad->available_amount) {
            throw new Exception('Insufficient crypto available in this advertisement.', 400);
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
            // Deduct available amount from ad immediately to prevent race conditions
            $ad->available_amount -= $cryptoAmount;
            if ($ad->available_amount < 0) {
                throw new Exception('Ad sold out.', 400);
            }
            $ad->save();

            // Create Trade
            $trade = P2PTrade::create([
                'advertisement_id' => $ad->id,
                'seller_id' => $seller->id,
                'buyer_id' => $buyer->id,
                'currency_id' => $ad->currency_id,
                'crypto_amount' => $cryptoAmount,
                'fiat_amount' => $fiatAmount,
                'status' => TradeStatus::PENDING,
            ]);

            // Move funds to escrow
            // recordDeposit expects: systemWallet, userWallet, amount, usdAmount, reference, description.
            // Wait, recordDeposit credits the user and debits system.
            // recordWithdrawal debits the user and credits system.
            // We want to debit the seller and credit the escrow. So recordWithdrawal for the seller.
            // Note: In LedgerService, recordWithdrawal takes (userWallet, systemWallet, ...).

            $reference = 'trade_'.$trade->id;
            $this->ledgerService->recordWithdrawal(
                userWallet: $sellerWallet,
                systemWallet: $escrowWallet,
                amount: $cryptoAmount,
                usdAmount: 0, // Simplified for P2P, normally calculate USD equivalent
                reference: $reference,
                description: 'P2P Trade Escrow Lock'
            );

            return $trade;
        });
    }
}
