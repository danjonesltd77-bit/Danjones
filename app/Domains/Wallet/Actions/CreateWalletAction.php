<?php

namespace App\Domains\Wallet\Actions;

use App\Models\User;
use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Contracts\SupportsWebhooksInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\HdWallet;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateWalletAction
{
    private CryptoGatewayInterface $cryptoGateway;

    public function __construct(CryptoGatewayInterface $cryptoGateway)
    {
        $this->cryptoGateway = $cryptoGateway;
    }

    public function execute(User $user, int $currencyId): void
    {
        $currency = Currency::findOrFail($currencyId);

        // Check if user already has a wallet for this currency to prevent duplicates
        if ($user->wallets()->where('currency_id', $currencyId)->exists()) {
            throw new Exception("User already has a {$currency->symbol} wallet.", 409);
        }

        // Logic for Fiat (Non-Crypto) Wallets
        if (!$currency->is_crypto) {
            $user->wallets()->create([
                'name' => $user->name,
                'currency_id' => $currency->id,
                'address' => $user->email,
                'status' => 'active'
            ]);

            return;
        }

        // Logic for Crypto Wallets
        $hdWallet = HdWallet::where('currency_id', $currency->id)->first();

        if (!$hdWallet) {
            throw new Exception("HD Wallet not found for currency {$currency->symbol}", 400);
        }

        // Address Generation
        $address = null;

        if ($currency->symbol === 'BTC') {
            $response = $this->cryptoGateway->generateBitcoinAddress($hdWallet->xpub, $hdWallet->index);
            $address = $response['address'] ?? null;
        } else {
            // TODO: Update CryptoGatewayInterface to include a generic generateAddress() method 
            // that handles ETH, TRX, DOGE, etc., based on the currency symbol.
            // Example: $response = $this->cryptoGateway->generateAddress($currency->symbol, $hdWallet->xpub, $hdWallet->index);
            throw new Exception("Gateway address generation for {$currency->symbol} is not yet implemented.", 501);
        }

        if (!$address) {
            throw new Exception("Failed to generate address for {$currency->symbol}", 500);
        }

        // Store the new Crypto Wallet
        $wallet = $user->wallets()->create([
            'name' => $user->name,
            'currency_id' => $currency->id,
            'address' => $address,
            'index' => $hdWallet->index,
            'status' => 'active'
        ]);

        // Increment HD Wallet Index
        $hdWallet->index += 1;
        $hdWallet->save();

        // Subscribe to incoming blockchain transactions
        if ($this->cryptoGateway instanceof SupportsWebhooksInterface) {
            $success = $this->cryptoGateway->subscribeToIncoming($wallet);
            if (!$success) {
                throw new Exception("Failed to create webhook subscription for {$currency->symbol}", 500);
            }
        }
    }
}
