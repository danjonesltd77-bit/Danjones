<?php

namespace Tests\Feature\Domains\Wallet\Gateways;

use App\Domains\Core\Services\SettingService;
use App\Domains\Wallet\Gateways\TatumCryptoGateway;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\HdWallet;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Models\Wallet;
use App\Domains\Wallet\Services\TatumApiClient;
use App\Enum\SystemWalletType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Response;
use Mockery;
use Tests\TestCase;

class TatumCryptoGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected TatumApiClient $apiClient;

    protected SettingService $settingService;

    protected TatumCryptoGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiClient = Mockery::mock(TatumApiClient::class);
        $this->settingService = Mockery::mock(SettingService::class);
        $this->gateway = new TatumCryptoGateway($this->apiClient, $this->settingService);
    }

    public function test_gaspump_batch_transfer_evm_chain_sends_fee_object_and_estimates_price()
    {
        $currency = Currency::factory()->create([
            'is_gaspump' => true,
            'symbol' => 'ETH',
            'token_currency' => 'ETH',
            'fee' => 0.0064,
            'token_address' => '0',
            'token_id' => '0',
            'contract_type' => 0,
        ]);

        $fromWallet = Wallet::factory()->create([
            'address' => '0xfrom_addr',
            'currency_id' => $currency->id,
            'index' => 1,
        ]);

        $gasWallet = SystemWallet::factory()->create([
            'address' => '0xgas_addr',
            'currency_id' => $currency->id,
            'type' => SystemWalletType::GAS,
        ]);

        $hdWallet = HdWallet::factory()->create([
            'private_key' => '0xpriv_key',
            'currency_id' => $currency->id,
        ]);

        // Mock estimate custodial fee endpoint returning failure to trigger fallback
        $estimateResponse = Mockery::mock(Response::class);
        $estimateResponse->shouldReceive('successful')->andReturn(false);
        $this->apiClient->shouldReceive('post')
            ->once()
            ->with('/blockchainOperations/gas', Mockery::any(), 'v4', true)
            ->andReturn($estimateResponse);

        // Mock batch transfer endpoint call and assert payload has 'fee' object with static fallback price (50 Gwei for ETH)
        $transferResponse = Mockery::mock(Response::class);
        $transferResponse->shouldReceive('successful')->andReturn(true);
        $transferResponse->shouldReceive('json')->andReturn(['signatureId' => 'sig_evm_123']);

        $this->apiClient->shouldReceive('post')
            ->once()
            ->with('/blockchain/sc/custodial/transfer/batch', Mockery::on(function ($payload) {
                return $payload['chain'] === 'ETH'
                    && $payload['custodialAddress'] === '0xfrom_addr'
                    && $payload['from'] === '0xgas_addr'
                    && ! isset($payload['feeLimit'])
                    && isset($payload['fee'])
                    && $payload['fee']['gasLimit'] === '300000'
                    && $payload['fee']['gasPrice'] === '50';
            }), 'v3', true)
            ->andReturn($transferResponse);

        $sigId = $this->gateway->gaspumpBatchTransfer(
            $fromWallet,
            ['0xto_addr'],
            ['1.0'],
            $gasWallet,
            $currency,
            $hdWallet
        );

        $this->assertEquals('sig_evm_123', $sigId);
    }

    public function test_gaspump_batch_transfer_evm_chain_uses_estimate_custodial_fee()
    {
        $currency = Currency::factory()->create([
            'is_gaspump' => true,
            'symbol' => 'ETH',
            'token_currency' => 'ETH',
            'fee' => 0.0064,
            'token_address' => '0xcontract_addr',
            'token_id' => '0',
            'contract_type' => 0,
        ]);

        $fromWallet = Wallet::factory()->create([
            'address' => '0xfrom_addr',
            'currency_id' => $currency->id,
            'index' => 1,
        ]);

        $gasWallet = SystemWallet::factory()->create([
            'address' => '0xgas_addr',
            'currency_id' => $currency->id,
            'type' => SystemWalletType::GAS,
        ]);

        $hdWallet = HdWallet::factory()->create([
            'private_key' => '0xpriv_key',
            'currency_id' => $currency->id,
        ]);

        // Mock /blockchainOperations/gas endpoint returning gasPrice = 50 Gwei (50000000000 Wei), gasLimit = 150000
        $estimateResponse = Mockery::mock(Response::class);
        $estimateResponse->shouldReceive('successful')->andReturn(true);
        $estimateResponse->shouldReceive('json')->andReturn([
            'gasLimit' => '150000',
            'gasPrice' => '50000000000',
        ]);

        $this->apiClient->shouldReceive('post')
            ->once()
            ->with('/blockchainOperations/gas', [
                'chain' => 'ETH',
                'from' => '0xgas_addr',
                'to' => '0xto_addr',
                'amount' => '1.0',
            ], 'v4', true)
            ->andReturn($estimateResponse);

        // Mock batch transfer endpoint call.
        // gasLimit for batch = gasLimit * 2 = 150000 * 2 = 300000
        // gasPrice buffered = gasPrice * 1.3 = 50 * 1.3 = 65 Gwei
        $transferResponse = Mockery::mock(Response::class);
        $transferResponse->shouldReceive('successful')->andReturn(true);
        $transferResponse->shouldReceive('json')->andReturn(['signatureId' => 'sig_evm_est_123']);

        $this->apiClient->shouldReceive('post')
            ->once()
            ->with('/blockchain/sc/custodial/transfer/batch', Mockery::on(function ($payload) {
                return $payload['chain'] === 'ETH'
                    && $payload['custodialAddress'] === '0xfrom_addr'
                    && $payload['from'] === '0xgas_addr'
                    && ! isset($payload['feeLimit'])
                    && isset($payload['fee'])
                    && $payload['fee']['gasLimit'] === '300000'
                    && $payload['fee']['gasPrice'] === '65';
            }), 'v3', true)
            ->andReturn($transferResponse);

        $sigId = $this->gateway->gaspumpBatchTransfer(
            $fromWallet,
            ['0xto_addr'],
            ['1.0'],
            $gasWallet,
            $currency,
            $hdWallet
        );

        $this->assertEquals('sig_evm_est_123', $sigId);
    }

    public function test_gaspump_batch_transfer_evm_chain_handles_string_gas_estimates_from_v4()
    {
        $currency = Currency::factory()->create([
            'is_gaspump' => true,
            'symbol' => 'ETH',
            'token_currency' => 'ETH',
            'fee' => 0.0064,
            'token_address' => '0xcontract_addr',
            'token_id' => '0',
            'contract_type' => 0,
        ]);

        $fromWallet = Wallet::factory()->create([
            'address' => '0xfrom_addr',
            'currency_id' => $currency->id,
            'index' => 1,
        ]);

        $gasWallet = SystemWallet::factory()->create([
            'address' => '0xgas_addr',
            'currency_id' => $currency->id,
            'type' => SystemWalletType::GAS,
        ]);

        $hdWallet = HdWallet::factory()->create([
            'private_key' => '0xpriv_key',
            'currency_id' => $currency->id,
        ]);

        // Mock /blockchainOperations/gas endpoint returning string values matching user sample
        $estimateResponse = Mockery::mock(Response::class);
        $estimateResponse->shouldReceive('successful')->andReturn(true);
        $estimateResponse->shouldReceive('json')->andReturn([
            'gasLimit' => '21000',
            'gasPrice' => '574543567',
        ]);

        $this->apiClient->shouldReceive('post')
            ->once()
            ->with('/blockchainOperations/gas', [
                'chain' => 'ETH',
                'from' => '0xgas_addr',
                'to' => '0xto_addr',
                'amount' => '1.0',
            ], 'v4', true)
            ->andReturn($estimateResponse);

        // Mock batch transfer endpoint call.
        // gasLimit for batch = 21000 * 2 = 42000
        // gasPrice buffered = (574543567 / 10^9) * 1.3 = 0.574543567 * 1.3 = 0.7469066371 Gwei
        // gasPriceGweiRounded = round(0.7469066371) = 1.0 Gwei
        $transferResponse = Mockery::mock(Response::class);
        $transferResponse->shouldReceive('successful')->andReturn(true);
        $transferResponse->shouldReceive('json')->andReturn(['signatureId' => 'sig_evm_est_456']);

        $this->apiClient->shouldReceive('post')
            ->once()
            ->with('/blockchain/sc/custodial/transfer/batch', Mockery::on(function ($payload) {
                return $payload['chain'] === 'ETH'
                    && $payload['custodialAddress'] === '0xfrom_addr'
                    && $payload['from'] === '0xgas_addr'
                    && ! isset($payload['feeLimit'])
                    && isset($payload['fee'])
                    && $payload['fee']['gasLimit'] === '42000'
                    && $payload['fee']['gasPrice'] === '1';
            }), 'v3', true)
            ->andReturn($transferResponse);

        $sigId = $this->gateway->gaspumpBatchTransfer(
            $fromWallet,
            ['0xto_addr'],
            ['1.0'],
            $gasWallet,
            $currency,
            $hdWallet
        );

        $this->assertEquals('sig_evm_est_456', $sigId);
    }

    public function test_gaspump_batch_transfer_tron_chain_sends_fee_limit()
    {
        $currency = Currency::factory()->create([
            'is_gaspump' => true,
            'symbol' => 'USDT',
            'token_currency' => 'TRON',
            'fee' => 1.0,
            'token_address' => '0',
            'token_id' => '0',
            'contract_type' => 0,
        ]);

        $fromWallet = Wallet::factory()->create([
            'address' => 'from_tron_addr',
            'currency_id' => $currency->id,
            'index' => 1,
        ]);

        $gasWallet = SystemWallet::factory()->create([
            'address' => 'gas_tron_addr',
            'currency_id' => $currency->id,
            'type' => SystemWalletType::GAS,
        ]);

        $hdWallet = HdWallet::factory()->create([
            'private_key' => 'tron_priv_key',
            'currency_id' => $currency->id,
        ]);

        // Mock batch transfer endpoint call and assert payload has 'feeLimit'
        $transferResponse = Mockery::mock(Response::class);
        $transferResponse->shouldReceive('successful')->andReturn(true);
        $transferResponse->shouldReceive('json')->andReturn(['signatureId' => 'sig_tron_123']);

        $this->apiClient->shouldReceive('post')
            ->once()
            ->with('/blockchain/sc/custodial/transfer/batch', Mockery::on(function ($payload) {
                return $payload['chain'] === 'TRON'
                    && $payload['custodialAddress'] === 'from_tron_addr'
                    && $payload['from'] === 'gas_tron_addr'
                    && $payload['feeLimit'] === 1.0
                    && ! isset($payload['fee']);
            }), 'v3', true)
            ->andReturn($transferResponse);

        $sigId = $this->gateway->gaspumpBatchTransfer(
            $fromWallet,
            ['to_tron_addr'],
            ['1.0'],
            $gasWallet,
            $currency,
            $hdWallet
        );

        $this->assertEquals('sig_tron_123', $sigId);
    }
}
