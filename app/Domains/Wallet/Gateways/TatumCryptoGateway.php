<?php

namespace App\Domains\Wallet\Gateways;

use App\Domains\Core\Services\SettingService;
use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Contracts\GaspumpServiceInterface;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Contracts\SupportsWebhooksInterface;
use App\Domains\Wallet\Contracts\WalletAccountInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\HdWallet;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Services\TatumApiClient;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TatumCryptoGateway implements CryptoGatewayInterface, MarketDataGatewayInterface, SupportsWebhooksInterface, GaspumpServiceInterface
{
    private TatumApiClient $apiClient;

    private SettingService $settingService;

    public function __construct(TatumApiClient $apiClient, SettingService $settingService)
    {
        $this->apiClient = $apiClient;
        $this->settingService = $settingService;
    }

    public function getBalance(string $identifier): float
    {
        return 0.0;
    }

    public function generateAddress(Currency $currency, HdWallet $hdWallet, ?string $chain = null, ?SystemWallet $gasWallet = null): string
    {
        if ($currency->is_gaspump) {
            if (! $gasWallet) {
                throw new \Exception("System gas wallet not configured for {$currency->symbol}", 500);
            }
            $response = $this->apiClient->post('/gas-pump', [
                'chain' => $chain,
                'owner' => $gasWallet->address,
                'from' => $hdWallet->index,
                'to' => $hdWallet->index,
            ], is_gaspump: true);

            if (! $response->successful()) {
                Log::error('Failed to generate crypto address', ['response' => $response->json()]);
                throw new \Exception('Failed to generate crypto address', 500);
            }

            return $response->json()[0];
        } else {
            if (! $hdWallet) {
                throw new \Exception("HD Wallet missing for {$currency->symbol}", 500);
            }
            $response = $this->apiClient->get("/{$currency->name}/address/{$hdWallet->xpub}/{$hdWallet->index}");

            if (! $response->successful()) {
                throw new \Exception($response->json()['message'] ?? 'Failed to generate crypto address', $response->status() ?: 500);
            }

            return $response->json()['address'];
        }
    }

    public function getTransactionDetails(string $txHash, Currency $currency): array
    {
        $response = $this->apiClient->get("/{$currency->name}/transaction/{$txHash}", is_gaspump: $currency->is_gaspump);

        if (! $response->successful()) {
            throw new \Exception($response->json()['message'] ?? 'Failed to get transaction details', $response->status() ?: 500);
        }

        return $response->json();
    }

    public function isTransactionConfirmed(string $txHash, Currency $currency): bool
    {
        try {
            if ($currency->is_gaspump) {
                return true;
            } else {
                $details = $this->getTransactionDetails($txHash, $currency);

                if (empty($details['blockNumber']) || $details['blockNumber'] == 0) {
                    return false; // Not mined yet (0 confirmations)
                }
                $currencyName = Str::lower($currency->name);
                // Fetch the current latest block height from Tatum to calculate confirmations natively
                $infoResponse = $this->apiClient->get("/{$currencyName}/info");

                if (! $infoResponse->successful() || empty($infoResponse->json()['blocks'])) {
                    return false;
                }

                $currentBlockHeight = $infoResponse->json()['blocks'];
                $confirmations = ($currentBlockHeight - $details['blockNumber']) + 1; // +1 includes the mined block itself

                return $confirmations >= 2;
            }
        } catch (\Exception $e) {
            Log::error("Failed to check transaction confirmation for hash {$txHash} on currency {$currency->id}: " . $e->getMessage());

            return false;
        }
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

        if ($chain == 'Dogecoin') {
            $chain = 'Doge';
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

        $response = $this->apiClient->post('/subscription', $payload, 'v4', is_gaspump: $currency->is_gaspump);

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
                $res = $this->apiClient->get('/tatum/rate/TRON?basePair=USD');
                $res = $res->json();
                $rate = $res['value'];
                break;
            case 4:
                $res = $this->apiClient->get('/tatum/rate/USDT?basePair=USD');
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

    public function getUsdNgnRate(): float
    {
        return $this->settingService->get('usd_ngn_rate', 1500.0);
    }

    function activateAddress(WalletAccountInterface $wallet, Currency $currency, HdWallet $hdWallet, SystemWallet $gasWallet)  {
        if (!$currency->is_gaspump) {
            throw new Exception("Gaspump transfer not supported for non-gaspump currency {$currency->symbol}", 500);
        }

        $chain = $currency->token_currency;
        if ($currency->parent_id != null) {
            $chain = $currency->parent->token_currency;
        }

        if ($gasWallet->balance < $currency->fee) {
            throw new Exception("Address activation not available", 500);
        }

        $payload = [
            'chain' => $chain,
            'owner' => $gasWallet->address,
            'from' => (int)$wallet->index,
            'to' => (int)$wallet->index,
            'signatureId' => $hdWallet->private_key,
        ];

        // if(env('APP_ENV') == 'production') {
        //     $payload['signatureId'] = $hdWallet->private_key;
        // }else{
        //     $payload['fromPrivateKey'] = "0ca1c3ba8b7596f5b64dd42be89cc476c3b91a25917e4c046a38e5db607000c1";
        // }

        if(!in_array($currency->id, [6,7,8])) {
            $payload['feeLimit'] = $currency->fee;
        }

        $response = $this->apiClient->post('/gas-pump/activate', $payload, 'v3', is_gaspump: true);
        Log::info($response->json());

        if (! $response->successful()) {
            Log::error('Failed to activate address', [
                'from' => $wallet->address,
                'response' => $response->json(),
            ]);

            throw new Exception('Failed to activate address', 500);
        }

        return;
    }

    public function multipleTransfer(WalletAccountInterface $from, array $recipient_addresses, array $amounts,
     SystemWallet $gasWallet, Currency $currency, HdWallet $hdWallet): string
    {
        if (!$currency->is_gaspump) {
            throw new \Exception("Gaspump transfer not supported for non-gaspump currency {$currency->symbol}", 500);
        }

        $chain = $currency->token_currency;
        if ($currency->parent_id != null) {
            $chain = $currency->parent->token_currency;
        }

        $tokenAddress = [];
        $tokenId = [];
        $contractType = [];

        foreach ($recipient_addresses as $address) {
            $tokenAddress[] = $currency->token_address;
            $tokenId[] = $currency->token_id;
            $contractType[] = (int)$currency->contract_type;
        }

        $payload = [
            'chain' => $chain,
            'custodialAddress' => $from->address,
            'recipient' => $recipient_addresses,
            'contractType' => $contractType,
            'amount' => $amounts,
            'signatureId' => $hdWallet->private_key,
            'from' => $gasWallet->address,
            'feeLimit' => $currency->fee,
            "tokenAddress" => $tokenAddress,
            "tokenId" => $tokenId
        ];

        $response = $this->apiClient->post('/blockchain/sc/custodial/transfer/batch', $payload, 'v3', is_gaspump: true);

        if (! $response->successful()) {
            Log::error('Failed to transfer from gas pump custodial address', [
                'from' => $from->address,
                'to' => $recipient_addresses,
                'response' => $response->json(),
            ]);
            throw new \Exception($response->json()['message'] ?? 'Failed to transfer funds', 500);
        }

        return $response->json()['signatureId'];
    }

}
