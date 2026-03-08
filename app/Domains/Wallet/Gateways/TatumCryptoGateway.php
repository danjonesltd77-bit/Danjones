<?php

namespace App\Domains\Wallet\Gateways;

use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Contracts\SupportsWebhooksInterface;
use App\Domains\Wallet\Contracts\WalletAccountInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\HdWallet;
use App\Domains\Wallet\Services\TatumApiClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TatumCryptoGateway implements CryptoGatewayInterface, SupportsWebhooksInterface, MarketDataGatewayInterface
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
        $currency = Currency::where('id', $currency_id)->first();

        switch ($currency_id) {
            case 2:
            case 5:
                $response = $this->apiClient->get("/{$currency->name}/address/{$hd_wallet->xpub}/{$hd_wallet->index}");
                break;

            default:
                throw new \Exception('Currency not available', 400);
        }

        if (! $response->successful()) {
            throw new \Exception($response->json()['message'] ?? 'Failed to generate crypto address', $response->status() ?: 500);
        }

        return $response->json();
    }

    public function getTransactionDetails(string $txHash, int $currency_id): array
    {

        switch ($currency_id) {
            case 2:
                $response = $this->apiClient->get("/bitcoin/transaction/{$txHash}");
                break;
            default:
                throw new \Exception('Currency not available', 400);
        }

        if (! $response->successful()) {
            throw new \Exception($response->json()['message'] ?? 'Failed to get transaction details', $response->status() ?: 500);
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

        if ($chain == "Dogecoin") {
            $chain = "Doge";
        }

        $network = 'mainnet';
        if (! $currency->is_gaspump && env('APP_ENV') != 'production') {
            $network = 'testnet';
        }

        $payload = [
            'type' => $type,
            'attr' => [
                'address' => $wallet->address,
                'chain' => Str::lower("$chain-$network"), // Should be dynamic based on $wallet->currency_id
                'url' => route('tatum.webhook.incoming'), // Assuming you have a webhook route setup
            ],
        ];

        $response = $this->apiClient->post('/subscription', $payload, 'v4');

        if (! $response->successful()) {
            Log::error('Failed to subscribe to incoming transactions', ['response' => $response->json()]);
            throw new \Exception('Failed to subscribe to incoming transactions', 500);
        }

        return $response->successful();
    }

    public function getExchangeRate(int $currency_id): float
    {
        $rate = 0;
        switch ($currency_id) {
            case 2:
                // $res = Http::get('https://data.binance.com/api/v3/ticker/price?symbol=BTCUSDT');
                // $rate = $res['price'];

                // $res = Http::get('https://api.coinbase.com/v2/prices/spot?currency=USD');
                // $res = $res->json();
                // $rate = $res['data']['amount'];

                $res = $this->apiClient->get('/tatum/rate/BTC?basePair=USD');
                $res = $res->json();
                $rate = $res['value'];
                break;
            case 3:
                $res = $this->apiClient->get('/tatum/rate/USDT?basePair=USD');
                $res = $res->json();
                $rate = $res['value'];
                break;
            case 4:
                $res = $this->apiClient->get('/tatum/rate/TRON?basePair=USD');
                $res = $res->json();
                $rate = $res['value'];
                break;
            case 5:
                $res = $this->apiClient->get('/tatum/rate/DOGE?basePair=USD');
                $res = $res->json();
                $rate = $res['value'];
                break;

            default:
                $currency = Currency::find($currency_id);

                $res = $this->apiClient->get("/tatum/rate/$currency->symbol?basePair=USD");
                $res = $res->json();
                $rate = $res['value'];
                break;
        }

        return $rate;
    }
}
