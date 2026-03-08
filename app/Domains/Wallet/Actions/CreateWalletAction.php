<?php

namespace App\Domains\Wallet\Actions;

use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Contracts\SupportsWebhooksInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\HdWallet;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Models\Wallet;
use App\Enum\SystemWalletType;
use App\Models\User;
use Exception;
use Illuminate\Validation\ValidationException;

class CreateWalletAction
{
    public function __construct(private CryptoGatewayInterface $cryptoGateway) {}

    /**
     * Create a wallet for the given user and currency.
     *
     * @throws ValidationException if the user already has a wallet for this currency.
     * @throws Exception if the HD wallet is missing or the gateway call fails.
     */
    public function execute(User $user, int $currencyId): Wallet
    {
        /** @var Currency $currency */
        $currency = Currency::findOrFail($currencyId);

        // Guard: prevent duplicate wallets per user per currency
        if ($user->wallets()->where('currency_id', $currencyId)->exists()) {
            throw new Exception("You already have a {$currency->symbol} wallet.", 400);
        }

        // Fiat / non-crypto wallets (e.g. NGN) — no gateway call needed
        if (! $currency->is_crypto) {
            return $user->wallets()->create([
                'currency_id' => $currencyId,
                'address'     => $user->email,
                'status'      => 'active',
            ]);
        }

        // Crypto wallet — derive address via HD wallet + gateway
        $hdWallet = HdWallet::where('currency_id', $currencyId)->first();

        if (! $hdWallet) {
            throw new Exception("HD Wallet not found for currency {$currency->symbol}", 500);
        }

        $gasWallet = null;
        $chain = $currency->token_currency;
        if ($currency->is_gaspump) {
            $gasWallet = SystemWallet::where('currency_id', $currencyId)
                ->where('type', SystemWalletType::GAS)
                ->first();
        }

        if($currency->parent_id != null){
            $chain = $currency->parent->token_currency;
        }

        $address = $this->cryptoGateway->generateAddress($currency, $hdWallet, $chain, $gasWallet);

        if (!$address) {
            throw new Exception("Failed to generate address for currency {$currency->symbol}", 500);
        }

        $status = 'active';
        if ($currency->is_gaspump == true) {
            $status = 'pending';
        }

        $wallet = $user->wallets()->create([
            'currency_id' => $currencyId,
            'address'     => $address,
            'index'       => $hdWallet->index,
            'status'      => $status,
        ]);

        $hdWallet->index += 1;
        $hdWallet->save();

        // Subscribe to incoming transactions if the gateway supports webhooks
        if ($this->cryptoGateway instanceof SupportsWebhooksInterface) {
            $success = $this->cryptoGateway->subscribeToIncoming($wallet);

            if (! $success) {
                throw new Exception("Failed to create webhook subscription for {$currency->symbol}", 500);
            }
        }

        return $wallet;
    }
}
