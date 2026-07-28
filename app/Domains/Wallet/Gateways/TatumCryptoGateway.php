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
use App\Domains\Wallet\Models\Wallet;
use App\Domains\Wallet\Services\TatumApiClient;
use App\Enum\SystemWalletType;
use App\Enum\WalletStatus;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TatumCryptoGateway implements CryptoGatewayInterface, GaspumpServiceInterface, MarketDataGatewayInterface, SupportsWebhooksInterface
{
    private TatumApiClient $apiClient;

    private SettingService $settingService;

    public function __construct(TatumApiClient $apiClient, SettingService $settingService)
    {
        $this->apiClient = $apiClient;
        $this->settingService = $settingService;
    }

    public function getBalance(string $address, Currency $currency): float
    {
        try {
            // If it's a token (has a parent currency)
            if ($currency->parent_id && $currency->parent_id != $currency->id) {
                $parentChain = Str::lower($currency->parent->name);

                $response = $this->apiClient->get("/blockchain/token/balance/{$parentChain}/{$currency->token_address}/{$address}", is_gaspump: true);

                if ($response->successful()) {
                    return (float) ($response->json()['balance'] ?? 0);
                }
            } else {
                $chain = Str::lower($currency->name);

                switch ($chain) {
                    case 'bitcoin':
                    case 'dogecoin':
                        $response = $this->apiClient->get("/{$chain}/address/balance/{$address}");
                        if ($response->successful()) {
                            $data = $response->json();

                            return (float) ($data['incoming'] ?? 0) - (float) ($data['outgoing'] ?? 0);
                        }
                        break;

                    case 'ethereum':
                        $response = $this->apiClient->get("/ethereum/account/balance/{$address}", is_gaspump: true);
                        if ($response->successful()) {
                            return (float) ($response->json()['balance'] ?? 0);
                        }
                        break;

                    case 'tron':
                        $response = $this->apiClient->get("/tron/account/{$address}", is_gaspump: true);
                        if ($response->successful()) {
                            return (float) ($response->json()['balance'] ?? 0) / 1000000;
                        }
                        break;

                    default:
                        $response = $this->apiClient->get("/{$chain}/account/balance/{$address}", is_gaspump: true);
                        if ($response->successful()) {
                            return (float) ($response->json()['balance'] ?? 0);
                        }
                        break;
                }
            }

            Log::warning("Failed to fetch balance for {$currency->symbol} at {$address}", [
                'response' => $response->json() ?? 'No response',
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching balance for {$currency->symbol}: ".$e->getMessage());
        }

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
        $chain = Str::lower($currency->name);
        if ($currency->parent != null) {
            $chain = Str::lower($currency->parent->name);
        }
        $response = $this->apiClient->get("/{$chain}/transaction/{$txHash}", is_gaspump: $currency->is_gaspump);

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
            Log::error("Failed to check transaction confirmation for hash {$txHash} on currency {$currency->id}: ".$e->getMessage());

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
            $type = 'INCOMING_FUNGIBLE_TX';
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

    public function activateAddress(WalletAccountInterface $wallet, Currency $currency, HdWallet $hdWallet, SystemWallet $gasWallet)
    {
        if (! $currency->is_gaspump) {
            throw new Exception("Gaspump transfer not supported for non-gaspump currency {$currency->symbol}", 500);
        }

        if ($wallet instanceof Wallet) {
            $alreadyActive = Wallet::where('address', $wallet->address)
                ->where('status', WalletStatus::ACTIVE)
                ->exists();

            if ($alreadyActive) {
                Wallet::where('address', $wallet->address)->update(['status' => WalletStatus::ACTIVE]);

                return;
            }
        }

        $chain = $currency->token_currency;
        if ($currency->parent_id != null) {
            $chain = $currency->parent->token_currency;
        }

        if ($gasWallet->balance < $currency->fee) {
            throw new Exception('Address activation not available', 500);
        }

        $payload = [
            'chain' => $chain,
            'owner' => $gasWallet->address,
            'from' => (int) $wallet->index,
            'to' => (int) $wallet->index,
            'signatureId' => $hdWallet->private_key,
        ];

        // if(env('APP_ENV') == 'production') {
        //     $payload['signatureId'] = $hdWallet->private_key;
        // }else{
        //     $payload['fromPrivateKey'] = "0ca1c3ba8b7596f5b64dd42be89cc476c3b91a25917e4c046a38e5db607000c1";
        // }

        if (! in_array($currency->id, [6, 7, 8])) {
            $payload['feeLimit'] = $currency->fee;
        }

        $response = $this->apiClient->post('/gas-pump/activate', $payload, 'v3', is_gaspump: true);

        if (! $response->successful()) {
            Log::error('Failed to activate address', [
                'from' => $wallet->address,
                'response' => $response->json(),
            ]);

            throw new Exception('Failed to activate address', 500);
        }

        if ($wallet instanceof Wallet) {
            Wallet::where('address', $wallet->address)->update(['status' => WalletStatus::ACTIVE]);
        }
    }

    public function isActivated(WalletAccountInterface $wallet, Currency $currency, ?SystemWallet $gasWallet = null): bool
    {
        if (! $currency->is_gaspump) {
            return true;
        }

        $chain = $currency->token_currency;
        if ($currency->parent_id != null) {
            $chain = $currency->parent->token_currency;
        }

        if (! $gasWallet) {
            $gasCurrencyId = $currency->parent_id ?: $currency->id;
            $gasWallet = SystemWallet::where('currency_id', $gasCurrencyId)
                ->where('type', SystemWalletType::GAS)
                ->first();
        }

        if (! $gasWallet || ! $gasWallet->address) {
            throw new Exception("Gas wallet configuration missing for currency {$currency->symbol}", 500);
        }

        $index = (int) $wallet->index;
        $owner = $gasWallet->address;

        $response = $this->apiClient->get("/gas-pump/activated/{$chain}/{$owner}/{$index}", 'v3', is_gaspump: true);

        if ($response->successful()) {
            return (bool) ($response->json()['activated'] ?? false);
        }

        Log::error('Failed to check gaspump activation status from Tatum', [
            'chain' => $chain,
            'owner' => $owner,
            'index' => $index,
            'response' => $response->json(),
        ]);

        throw new Exception($response->json()['message'] ?? 'Failed to check gaspump activation status from Tatum.', 500);
    }

    public function gaspumpBatchTransfer(WalletAccountInterface $from, array $recipient_addresses, array $amounts,
        SystemWallet $gasWallet, Currency $currency, HdWallet $hdWallet): string
    {
        if (! $currency->is_gaspump) {
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
            $contractType[] = (int) $currency->contract_type;
        }

        $payload = [
            'chain' => $chain,
            'custodialAddress' => $from->address,
            'recipient' => $recipient_addresses,
            'contractType' => $contractType,
            'amount' => $amounts,
            'signatureId' => $hdWallet->private_key,
            'from' => $gasWallet->address,
            'tokenAddress' => $tokenAddress,
            'tokenId' => $tokenId,
        ];

        if ($chain !== 'TRON') {
            $totalAmount = array_sum($amounts);
            $estimate = $this->estimateCustodialFee($currency, $from->address, $recipient_addresses[0], $totalAmount, true);

            if ($estimate['success']) {
                $payload['fee'] = [
                    'gasLimit' => (string) $estimate['gasLimit'],
                    'gasPrice' => (string) $estimate['gasPriceGwei'],
                ];
            } else {
                $payload['fee'] = [
                    'gasLimit' => '300000',
                    'gasPrice' => $chain === 'BSC' ? '5' : '50',
                ];
            }
        } else {
            $payload['feeLimit'] = $currency->fee;
        }

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

    public function estimateOnchainFee(Currency $currency, float $amount): float
    {
        $symbol = Str::upper($currency->symbol);

        // 1. Determine input count for UTXO chains by looking up balances internally
        $inputCount = 1;
        if (! $currency->is_gaspump) {
            $changeWallet = SystemWallet::where('currency_id', $currency->id)
                ->where('type', SystemWalletType::CHANGE)
                ->first();

            $walletsToSelectFrom = collect();

            if ($changeWallet) {
                // We use the gateway's own getBalance to ensure consistency
                $onchainBalance = $this->getBalance($changeWallet->address, $currency);
                if ($onchainBalance > 0) {
                    $walletsToSelectFrom->push((object) ['balance' => $onchainBalance]);
                }
            }

            $dbWallets = Wallet::where('currency_id', $currency->id)
                ->where('address_balance', '>', 0)
                ->get();

            foreach ($dbWallets as $wallet) {
                $walletsToSelectFrom->push((object) ['balance' => (float) $wallet->address_balance]);
            }

            $sortedWallets = $walletsToSelectFrom->sortByDesc('balance');
            $currentBalance = 0;
            $foundInputs = 0;

            foreach ($sortedWallets as $wallet) {
                $foundInputs++;
                $currentBalance += $wallet->balance;
                if ($currentBalance >= $amount) {
                    break;
                }
            }

            $inputCount = max(1, $foundInputs);
        }

        switch ($symbol) {
            case 'BTC':
                $response = $this->apiClient->get("/blockchain/fee/{$symbol}", 'v3');

                if (! $response->successful()) {
                    Log::error('Tatum fee estimation failed', [
                        'currency' => $currency->symbol,
                        'response' => $response->json(),
                    ]);
                    throw new \Exception($response->json()['message'] ?? 'Could not estimate transaction fee.', 500);
                }

                $feeRate = (float) ($response->json()['slow'] ?? 0);

                // Native SegWit (P2WPKH) size in vBytes:
                // inputs are ~68 vBytes, outputs are ~31 vBytes, overhead is ~10.5 vBytes
                $estimatedSize = (int) ceil(($inputCount * 68) + (2 * 31) + 10.5);
                $totalSatoshis = $feeRate * $estimatedSize;

                $networkFee = $totalSatoshis / 100000000;
                $serviceFee = (float) $this->settingService->get('send_fee_'.Str::lower($symbol), $currency->fee);

                return $networkFee + $serviceFee;

            case 'DOGE':
                $response = $this->apiClient->get("/blockchain/fee/{$symbol}", 'v3');

                if (! $response->successful()) {
                    Log::error('Tatum fee estimation failed', [
                        'currency' => $currency->symbol,
                        'response' => $response->json(),
                    ]);
                    throw new \Exception($response->json()['message'] ?? 'Could not estimate transaction fee.', 500);
                }

                $feeRate = (float) ($response->json()['slow'] ?? 0);

                // Legacy P2PKH size in bytes:
                // inputs are ~148 bytes, outputs are ~34 bytes, overhead is ~10 bytes
                $estimatedSize = ($inputCount * 148) + (2 * 34) + 10;
                $totalSatoshis = $feeRate * $estimatedSize;

                $networkFee = $totalSatoshis / 100000000;
                // $serviceFee = (float) $this->settingService->get('send_fee_'.Str::lower($symbol), $currency->fee);
                $serviceFee = 1;

                return $networkFee + $serviceFee;

            default:
                $resultFee = (float) $this->settingService->get('send_fee_'.Str::lower($symbol), $currency->fee);

                return $resultFee;
        }

        return 0;
    }

    public function utxoSend(Currency $currency, string $to, float $amount, float $fee): array
    {
        $changeWallet = SystemWallet::where('currency_id', $currency->id)
            ->where('type', SystemWalletType::CHANGE)
            ->first();

        if (! $changeWallet) {
            throw new \Exception('Change wallet not found for currency '.$currency->symbol, 500);
        }

        $walletsToSelectFrom = collect();
        if ($changeWallet) {
            $bal = $this->getBalance($changeWallet->address, $currency);
            if ($bal > 0) {
                $walletsToSelectFrom->push((object) [
                    'address' => $changeWallet->address,
                    'address_balance' => $bal,
                    'index' => 0,
                    'is_system' => true,
                ]);
            }
        }

        $dbWallets = Wallet::where('currency_id', $currency->id)
            ->where('address_balance', '>', 0)
            ->get();

        foreach ($dbWallets as $w) {
            $walletsToSelectFrom->push((object) [
                'address' => $w->address,
                'address_balance' => (float) $w->address_balance,
                'index' => (int) $w->index,
                'is_system' => false,
                'model' => $w,
            ]);
        }

        $sorted = $walletsToSelectFrom->sortByDesc('address_balance');
        $selected = collect();
        $runningBal = 0;
        foreach ($sorted as $w) {
            $selected->push($w);
            $runningBal += $w->address_balance;
            if ($runningBal >= ($amount + $fee)) {
                break;
            }
        }

        if ($runningBal < ($amount + $fee)) {
            throw new Exception('Service not available');
        }

        $hd = $currency->hdWallet;
        $formattedWallets = $selected->map(fn ($w) => [
            'address' => $w->address,
            'signatureId' => $hd->signature_id,
            'index' => (int) $w->index,
        ])->values()->toArray();

        $chain = Str::lower($currency->name);

        $payload = [
            'fromAddress' => $formattedWallets,
            'to' => [
                [
                    'address' => $to,
                    'value' => round($amount, 6),
                ],
            ],
            'fee' => number_format($fee, 8, '.', ''),
        ];

        if ($changeWallet) {
            $payload['changeAddress'] = $changeWallet->address;
        }

        $response = $this->apiClient->post("/{$chain}/transaction", $payload);

        if (! $response->successful()) {
            Log::error('Tatum on-chain send failed', [
                'chain' => $chain,
                'response' => $response->json(),
            ]);
            throw new Exception($response->json()['message'] ?? 'Failed to broadcast on-chain transaction.', 500);
        }

        $data = $response->json();
        $data['spentAddresses'] = $selected->where('is_system', false)->pluck('address')->toArray();

        return $data;
    }

    public function estimateCustodialFee(Currency $currency, string $sender_address, string $recipient_address, float $amount, bool $isBatch = false): array
    {
        $gasCurrencyId = $currency->parent_id ?: $currency->id;
        $gasWallet = SystemWallet::where('currency_id', $gasCurrencyId)
            ->where('type', SystemWalletType::GAS)
            ->first();

        if (! $gasWallet) {
            Log::warning("Gas wallet not found when estimating custodial fee for {$currency->symbol}");

            return ['success' => false];
        }

        $chain = $currency->token_currency;
        if ($currency->parent_id != null) {
            $chain = $currency->parent->token_currency;
        }

        try {
            $response = $this->apiClient->post('/blockchainOperations/gas', [
                'chain' => $chain,
                'from' => $gasWallet->address,
                'to' => $recipient_address,
                'amount' => (string) $amount,
            ], 'v4', is_gaspump: true);

            if ($response->successful()) {
                $data = $response->json();

                $gasLimit = isset($data['gasLimit']) ? (float) $data['gasLimit'] : 300000;
                $gasPriceWei = isset($data['gasPrice']) ? (float) $data['gasPrice'] : 20000000000;
                $gasPriceGwei = $gasPriceWei / (10 ** 9);

                if ($isBatch) {
                    $gasLimit = $gasLimit * 2;
                }

                // Add a comfortable 30% gas price buffer to handle sudden block base fee spikes
                $bufferedGasPriceGwei = $gasPriceGwei * 1.3;

                // Round to integer Gwei for the fee object
                $gasPriceGweiRounded = round($bufferedGasPriceGwei);
                if ($gasPriceGweiRounded < 1) {
                    $gasPriceGweiRounded = 1;
                }

                // For EVM chains, feeLimit is expressed in native token units (e.g. ETH). 1 native token = 10^9 Gwei.
                $feeLimit = ($gasLimit * $bufferedGasPriceGwei) / (10 ** 9);

                return [
                    'success' => true,
                    'feeLimit' => $feeLimit,
                    'gasLimit' => (int) round($gasLimit),
                    'gasPriceGwei' => (int) $gasPriceGweiRounded,
                    'gasPrice' => $bufferedGasPriceGwei * (10 ** 9),
                ];
            }

            Log::warning('Tatum fee estimation returned status: '.$response->status(), ['body' => $response->body()]);
        } catch (\Exception $e) {
            Log::error('Exception in estimateCustodialFee: '.$e->getMessage());
        }

        return ['success' => false];
    }
}
