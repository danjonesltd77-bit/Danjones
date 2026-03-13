<?php

namespace App\Domains\Wallet\Actions;

use App\Models\User;
use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Contracts\SupportsWebhooksInterface;
use App\Domains\Wallet\Models\HdWallet;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateDefaultWalletsAction
{
    private CryptoGatewayInterface $cryptoGateway;

    public function __construct(CryptoGatewayInterface $cryptoGateway)
    {
        $this->cryptoGateway = $cryptoGateway;
    }

    public function execute(User $user): void
    {
        // Setup NGN Wallet (Currency ID 1)
        $user->wallets()->create([
            'name' => $user->name,
            'currency_id' => 1,
            'address' => $user->email,
            'status' => 'active'
        ]);

        // Setup BTC Wallet (Currency ID 2)
        $btcHd = HdWallet::where('currency_id', 2)->first();

        if (!$btcHd) {
            throw new Exception('HD Wallet not found for currency BTC', 400);
        }

        // Tatum Gateway abstracting external API calls
        $response = $this->cryptoGateway->generateAddress($btcHd->currency, $btcHd);

        $btcAddress = $response;

        $btcWallet = $user->wallets()->create([
            'name' => $user->name,
            'currency_id' => 2,
            'address' => $btcAddress,
            'index' => $btcHd->index,
            'status' => 'active'
        ]);

        $btcHd->index += 1;
        $btcHd->save();

        // Subscribe the wallet
        if ($this->cryptoGateway instanceof SupportsWebhooksInterface) {
            $success = $this->cryptoGateway->subscribeToIncoming($btcWallet);

            if (!$success) {
                throw new Exception('Failed to create subscription', 500);
            }
        }
    }
}
