<?php

namespace Tests\Feature\Domains\Wallet\Api;

use App\Domains\Core\Services\SettingService;
use App\Domains\Wallet\Actions\SendAction;
use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Contracts\GaspumpServiceInterface;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\HdWallet;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Models\Wallet;
use App\Domains\Wallet\Services\LedgerService;
use App\Enum\SystemWalletType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SendActionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $gateway;

    protected $marketData;

    protected $settings;

    protected $ledger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->gateway = Mockery::mock(CryptoGatewayInterface::class);
        $this->marketData = Mockery::mock(MarketDataGatewayInterface::class);
        $this->settings = Mockery::mock(SettingService::class);
        $this->ledger = Mockery::mock(LedgerService::class);
    }

    public function test_send_action_handles_gaspump_correctly()
    {
        $currency = Currency::factory()->create(['is_gaspump' => true, 'symbol' => 'USDT', 'decimal' => 6]);
        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'currency_id' => $currency->id,
            'balance' => 1000.0,
            'address' => 'user_addr',
        ]);

        $feeWallet = SystemWallet::factory()->create([
            'currency_id' => $currency->id,
            'type' => SystemWalletType::FEE,
            'address' => 'fee_addr',
        ]);

        $gasWallet = SystemWallet::factory()->create([
            'currency_id' => $currency->id,
            'type' => SystemWalletType::GAS,
            'address' => 'gas_addr',
        ]);

        $hdWallet = HdWallet::factory()->create(['currency_id' => $currency->id]);
        $currency->setRelation('hdWallet', $hdWallet);

        $this->settings->shouldReceive('get')->with('send_fee_usdt', Mockery::any())->andReturn(1.0);
        $this->gateway->shouldReceive('estimateOnchainFee')->andReturn(1.0);
        $this->marketData->shouldReceive('getExchangeRate')->andReturn(1.0);

        $gaspumpMock = Mockery::mock(GaspumpServiceInterface::class);
        $this->app->instance(GaspumpServiceInterface::class, $gaspumpMock);

        $gaspumpMock->shouldReceive('gaspumpBatchTransfer')
            ->once()
            ->with(\Mockery::any(), \Mockery::any(), \Mockery::any(), \Mockery::any(), \Mockery::any(), \Mockery::any())
            ->andReturn('tx_123');

        $this->ledger->shouldReceive('recordWithdrawal')->once();
        $this->ledger->shouldReceive('recordFee')->once();

        $action = new SendAction($this->gateway, $this->ledger, $this->marketData, $this->settings);
        $res = $action->execute($this->user, $currency->id, 10, 'recipient_addr');

        $this->assertEquals('tx_123', $res['txId']);
    }

    public function test_send_action_handles_utxo_correctly()
    {
        $currency = Currency::factory()->create(['is_gaspump' => false, 'symbol' => 'BTC', 'decimal' => 8]);
        $wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'currency_id' => $currency->id,
            'balance' => 1.0,
            'address' => 'user_addr',
            'address_balance' => 0.5,
        ]);

        $feeWallet = SystemWallet::factory()->create([
            'currency_id' => $currency->id,
            'type' => SystemWalletType::FEE,
            'address' => 'fee_addr',
        ]);

        $changeWallet = SystemWallet::factory()->create([
            'currency_id' => $currency->id,
            'type' => SystemWalletType::CHANGE,
            'address' => 'change_addr',
        ]);

        $hdWallet = HdWallet::factory()->create(['currency_id' => $currency->id]);
        $currency->setRelation('hdWallet', $hdWallet);

        $this->settings->shouldReceive('get')->with('send_fee_btc', Mockery::any())->andReturn(0.0001);
        $this->gateway->shouldReceive('estimateOnchainFee')->andReturn(0.0005); // 0.0001 service + 0.0004 network
        $this->marketData->shouldReceive('getExchangeRate')->andReturn(60000.0);
        $this->gateway->shouldReceive('getBalance')->andReturn(0.0);

        $this->gateway->shouldReceive('utxoSend')
            ->once()
            ->with(\Mockery::any(), \Mockery::any(), \Mockery::any(), \Mockery::any())
            ->andReturn(['txId' => 'tx_utxo', 'spentAddresses' => ['user_addr']]);

        $this->marketData->shouldReceive('getExchangeRate')->andReturn(1.0);

        $this->ledger->shouldReceive('recordWithdrawal')->once();
        $this->ledger->shouldReceive('recordFee')->once();

        $action = new SendAction($this->gateway, $this->ledger, $this->marketData, $this->settings);
        $res = $action->execute($this->user, $currency->id, 0.1, 'recipient_addr');

        $this->assertEquals('tx_utxo', $res['txId']);
        $this->assertEquals(0, $wallet->fresh()->address_balance);
    }
}
