<?php

namespace App\Domains\P2P\Actions;

use App\Domains\P2P\Models\P2PAdvertisement;
use App\Domains\Wallet\Contracts\GaspumpServiceInterface;
use App\Domains\Wallet\Models\SystemWallet;
use App\Enum\AdvertisementType;
use App\Enum\SystemWalletType;
use App\Enum\WalletStatus;
use App\Models\User;
use Exception;

class CreateAdvertisementAction
{
    public function __construct(private GaspumpServiceInterface $gaspumpService) {}

    /**
     * Create a new P2P advertisement.
     *
     * @throws Exception
     */
    public function execute(User $user, array $data): P2PAdvertisement
    {
        $wallet = $user->wallet($data['currency_id']);

        if (! $wallet) {
            throw new Exception('You do not have a wallet for this currency.', 400);
        }

        // Activation for gaspump currencies
        if ($wallet->currency->is_gaspump) {
            $gasCurrencyId = $wallet->currency->parent_id ?: $wallet->currency_id;
            $gasWallet = SystemWallet::where('currency_id', $gasCurrencyId)
                ->where('type', SystemWalletType::GAS)
                ->firstOrFail();

            if ($wallet->status === WalletStatus::PENDING) {
                $this->gaspumpService->activateAddress(
                    $wallet,
                    $wallet->currency,
                    $wallet->currency->hdWallet,
                    $gasWallet
                );

                $wallet->status = WalletStatus::ACTIVE;
                $wallet->save();

                throw new Exception('Wallet not activated, please retry in 5 minutes', 400);
            }
        }

        // Cumulative check: Total advertised amount across all active ads cannot exceed balance.
        // Pending trades already deduct from balance immediately in InitiateTradeAction.
        if ($data['type'] === AdvertisementType::SELL->value) {
            $totalAdvertisedAmount = $user->p2pAdvertisements()
                ->where('currency_id', $data['currency_id'])
                ->where('type', AdvertisementType::SELL->value)
                ->where('is_active', true)
                ->sum('available_amount');

            if ($wallet->balance < ($totalAdvertisedAmount + $data['total_amount'])) {
                throw new Exception('Insufficient crypto balance. Your active advertisements already commit a portion of your balance.', 400);
            }
        }

        // Note: We don't deduct balance on ad creation, only on trade initiation (escrow lock).
        // This allows users to have ads without locking funds until an actual trade starts.
        // Alternatively, funds could be locked in escrow immediately. Here, we lock on trade start.

        return $user->p2pAdvertisements()->create([
            'currency_id' => $data['currency_id'],
            'bank_account_id' => $data['bank_account_id'] ?? null,
            'type' => $data['type'],
            'price' => $data['price'],
            'total_amount' => $data['total_amount'],
            'available_amount' => $data['total_amount'], // Initially, available is total
            'min_limit' => $data['min_limit'],
            'max_limit' => $data['max_limit'],
            'terms' => $data['terms'] ?? null,
            'is_active' => true,
        ]);
    }
}
