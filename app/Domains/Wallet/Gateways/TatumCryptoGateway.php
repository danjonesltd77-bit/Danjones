<?php

namespace App\Domains\Wallet\Gateways;

use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Contracts\SupportsWebhooksInterface;
use App\Domains\Wallet\Contracts\WalletAccountInterface;
use App\Domains\Wallet\Models\HdWallet;
use App\Domains\Wallet\Services\TatumApiClient;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TatumCryptoGateway implements CryptoGatewayInterface, SupportsWebhooksInterface
{
    private TatumApiClient $apiClient;

    public function __construct(TatumApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getBalance(string $identifier): float
    {
        return 0.0;
    }

    public function generateAddress(int $currency_id): array
    {
        $hd_wallet = HdWallet::where('currency_id', $currency_id)->first();

        switch ($currency_id) {
            case 2:
                $response = $this->apiClient->get("/bitcoin/address/{$hd_wallet->xpub}/{$hd_wallet->index}");
                break;
            // case 3:
            //     $response = $this->apiClient->get("/ethereum/address/{$hd_wallet->xpub}/{$hd_wallet->index}");
            //     break;
            // case 4:
            //     $response = $this->apiClient->get("/tron/address/{$hd_wallet->xpub}/{$hd_wallet->index}");
            //     break;
            default:
                throw new \Exception('Currency not available', 400);
        }

        if (!$response->successful()) {
            throw new \Exception($response->json()['message'] ?? 'Failed to generate crypto address', $response->status() ?: 500);
        }



        return $response->json();
    }

    public function subscribeToIncoming(WalletAccountInterface $wallet): bool
    {
        $currency = $wallet->currency;
        $chain = $currency->name;

        if ($currency->parent_id == $currency->id || $currency->parent_id == null) {
            $type = 'INCOMING_NATIVE_TX';
        } else {
            $type = 'INCOMING_INTERNAL_TX';
            $chain = $currency->parent->name;
        }

        $network = 'mainnet';
        if (!$currency->is_gaspump && env('APP_ENV') != 'production') {
            $network = 'testnet';
        }

        $payload = [
            'type' => $type,
            'attr' => [
                'address' => $wallet->address,
                'chain' => Str::lower("$chain-$network"), // Should be dynamic based on $wallet->currency_id
                'url' => route('tatum.webhook.incoming') // Assuming you have a webhook route setup
            ]
        ];

        $response = $this->apiClient->post('/subscription', $payload, 'v4');


        return $response->successful();
    }
}
