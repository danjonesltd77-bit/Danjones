<?php

namespace App\Domains\Wallet\Actions;

use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Contracts\SupportsWebhooksInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\HdWallet;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Models\Wallet;
use App\Enum\SystemWalletType;
use App\Enum\WalletStatus;
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
            $wallet = $user->wallets()->create([
                'currency_id' => $currencyId,
                'address' => $user->email,
                'status' => WalletStatus::ACTIVE,
            ]);

            send_notification($user, 'Wallet Created', "Your {$currency->name} wallet has been successfully created.", 'wallet_created', ['wallet_id' => $wallet->id, 'currency' => $currency->symbol]);

            return $wallet;
        }

        // Crypto wallet — derive address via HD wallet + gateway
        $hdWallet = HdWallet::where('currency_id', $currencyId)->first();

        if (! $hdWallet) {
            throw new Exception("HD Wallet not found for currency {$currency->symbol}", 500);
        }

        $gasWallet = null;
        $chain = $currency->token_currency;
        if ($currency->is_gaspump) {
            $gasCurrencyId = $currency->parent_id ?: $currencyId;
            $gasWallet = SystemWallet::where('currency_id', $gasCurrencyId)
                ->where('type', SystemWalletType::GAS)
                ->first();
        }

        if ($currency->parent_id != null) {
            $chain = $currency->parent->token_currency;

            $parentCurrency = Currency::findOrFail($currency->parent_id);

            if (! $user->wallets()->where('currency_id', $parentCurrency->id)->exists()) {
                $parentWallet = $this->execute($user, $parentCurrency->id);
            } else {
                $parentWallet = $user->wallets()->where('currency_id', $parentCurrency->id)->first();
            }

            $address = $parentWallet->address;
            $index = $parentWallet->index;
        } else {
            $address = $this->cryptoGateway->generateAddress($currency, $hdWallet, $chain, $gasWallet);
            $index = $hdWallet->index;
        }

        if (! $address) {
            throw new Exception("Failed to generate address for currency {$currency->symbol}", 500);
        }

        $status = WalletStatus::ACTIVE;
        if ($currency->is_gaspump == true) {
            $status = WalletStatus::PENDING;
        }

        $wallet = $user->wallets()->create([
            'currency_id' => $currencyId,
            'address' => $address,
            'index' => $index,
            'status' => $status,
        ]);

        if ($currency->parent_id == null) {
            $hdWallet->index += 1;
            $hdWallet->save();
        }

        // Subscribe to incoming transactions if the gateway supports webhooks
        if ($this->cryptoGateway instanceof SupportsWebhooksInterface) {
            $success = $this->cryptoGateway->subscribeToIncoming($wallet);

            if (! $success) {
                throw new Exception("Failed to create webhook subscription for {$currency->symbol}", 500);
            }
        }

        send_notification($user, 'Wallet Created', "Your {$currency->name} wallet has been successfully created.", 'wallet_created', ['wallet_id' => $wallet->id, 'currency' => $currency->symbol]);

        return $wallet;
    }
}
