<?php

namespace App\Domains\P2P\Actions;

use App\Domains\Core\Services\SettingService;
use App\Domains\P2P\Models\P2PTrade;
use App\Domains\Wallet\Actions\CreateWalletAction;
use App\Domains\Wallet\Contracts\GaspumpServiceInterface;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Services\LedgerService;
use App\Enum\SystemWalletType;
use App\Enum\TradeStatus;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class CompleteTradeAction
{
    public function __construct(
        protected LedgerService $ledgerService,
        protected CreateWalletAction $createWalletAction,
        protected GaspumpServiceInterface $gaspumpService,
        protected SettingService $settingService
    ) {}

    /**
     * @throws Exception
     */
    public function execute(User $user, P2PTrade $trade, bool $isAdmin = false): P2PTrade
    {
        if (! $isAdmin && $trade->status !== TradeStatus::PAID && $trade->status !== TradeStatus::PENDING) {
            throw new Exception('Trade cannot be completed from its current state.', 400);
        }

        if ($isAdmin && $trade->status !== TradeStatus::DISPUTED && $trade->status !== TradeStatus::PAID && $trade->status !== TradeStatus::PENDING) {
            throw new Exception('Admin can only complete pending, paid or disputed trades.', 400);
        }

        if (! $isAdmin && $trade->seller_id !== $user->id) {
            throw new Exception('Only the seller can release the crypto.', 403);
        }

        $escrowWallet = SystemWallet::where('currency_id', $trade->currency_id)
            ->where('type', SystemWalletType::ESCROW)
            ->first();

        if (! $escrowWallet) {
            throw new Exception('System escrow wallet not found.', 500);
        }

        return DB::transaction(function () use ($trade, $escrowWallet) {
            // Re-fetch and lock the trade to ensure it hasn't already been completed
            $lockedTrade = P2PTrade::where('id', $trade->id)->lockForUpdate()->firstOrFail();
            
            if ($lockedTrade->status === TradeStatus::COMPLETED) {
                return $lockedTrade;
            }

            $lockedTrade->status = TradeStatus::COMPLETED;
            $lockedTrade->save();

            $buyer = $lockedTrade->buyer;
            $buyerWallet = $buyer->wallet($lockedTrade->currency_id);

            // Create wallet for buyer if they don't have one
            if (! $buyerWallet) {
                $buyerWallet = $this->createWalletAction->execute($buyer, $lockedTrade->currency_id);
            }
            
            $lockedBuyerWallet = \App\Domains\Wallet\Models\Wallet::where('id', $buyerWallet->id)->lockForUpdate()->firstOrFail();

            $feePercentage = $this->settingService->get('p2p_fee_percentage', 0);
            $feeAmount = (float) $lockedTrade->crypto_amount * ($feePercentage / 100);
            $netAmount = (float) $lockedTrade->crypto_amount - $feeAmount;

            $reference = 'trade_release_'.$lockedTrade->id;

            $feeWallet = SystemWallet::where('currency_id', $lockedTrade->currency_id)
                ->where('type', SystemWalletType::FEE)
                ->first();

            // If it's a gaspump currency, we need to move the crypto on-chain
            if ($lockedTrade->currency->is_gaspump) {
                $gasWallet = SystemWallet::where('currency_id', $lockedTrade->currency_id)
                    ->where('type', SystemWalletType::GAS)
                    ->firstOrFail();

                $recipients = [$lockedBuyerWallet->address];
                $amounts = [(string) $netAmount];

                if ($feeAmount > 0 && $feeWallet && $feeWallet->address) {
                    $recipients[] = $feeWallet->address;
                    $amounts[] = (string) $feeAmount;
                }

                // For P2P release, we transfer from seller's custodial address to recipients
                $this->gaspumpService->gaspumpBatchTransfer(
                    $lockedTrade->seller->wallet($lockedTrade->currency_id),
                    $recipients,
                    $amounts,
                    $gasWallet,
                    $lockedTrade->currency,
                    $lockedTrade->currency->hdWallet
                );

                // For gaspump, we only debit the escrow wallet for the net amount on the ledger.
                // The buyer will be credited via a webhook later.
                $this->ledgerService->recordWithdrawal(
                    userWallet: $escrowWallet,
                    systemWallet: null,
                    amount: $netAmount,
                    usdAmount: 0,
                    reference: $reference,
                    description: 'P2P Trade Escrow Release (On-chain)'
                );

                // Record the fee on the ledger: Escrow -> Fee Wallet
                if ($feeAmount > 0 && $feeWallet) {
                    $this->ledgerService->recordDeposit(
                        systemWallet: $escrowWallet,
                        userWallet: $feeWallet,
                        amount: $feeAmount,
                        usdAmount: 0,
                        reference: $reference.'_fee',
                        description: 'P2P Trade Fee'
                    );
                }
            } else {
                // Record the net amount release: Escrow -> Buyer
                $this->ledgerService->recordDeposit(
                    systemWallet: $escrowWallet,
                    userWallet: $lockedBuyerWallet,
                    amount: $netAmount,
                    usdAmount: 0,
                    reference: $reference,
                    description: 'P2P Trade Crypto Release'
                );

                // Record the fee: Escrow -> Fee Wallet
                if ($feeAmount > 0 && $feeWallet) {
                    $this->ledgerService->recordDeposit(
                        systemWallet: $escrowWallet,
                        userWallet: $feeWallet,
                        amount: $feeAmount,
                        usdAmount: 0,
                        reference: $reference.'_fee',
                        description: 'P2P Trade Fee'
                    );
                }
            }

            return $lockedTrade;
        });
    }
}
