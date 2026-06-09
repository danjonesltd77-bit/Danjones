<?php

use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\Wallet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->marketDataMock = Mockery::mock(MarketDataGatewayInterface::class);
    $this->app->instance(MarketDataGatewayInterface::class, $this->marketDataMock);

    $this->user = User::factory()->create();

    // Create BTC currency
    $this->btc = Currency::create([
        'name' => 'Bitcoin',
        'symbol' => 'BTC',
        'coingecko_id' => 'bitcoin',
        'is_crypto' => true,
        'is_active' => true,
        'decimal' => 8,
    ]);

    // Create NGN currency
    $this->ngn = Currency::create([
        'name' => 'Naira',
        'symbol' => 'NGN',
        'is_crypto' => false,
        'is_active' => true,
        'decimal' => 2,
    ]);
});

test('it can update currency price change from coingecko', function () {
    Http::fake([
        'https://api.coingecko.com/api/v3/simple/price*' => Http::response([
            'bitcoin' => [
                'usd' => 60000.0,
                'usd_24h_change' => 5.5432,
            ],
        ], 200),
    ]);

    $this->artisan('wallet:update-price-change')
        ->assertExitCode(0);

    $this->assertDatabaseHas('currencies', [
        'id' => $this->btc->id,
        'price_change_24h' => 5.5432,
    ]);
});

test('it calculates dashboard PnL amount and percentage correctly', function () {
    // 10% price change (price_change_24h is stored as a percentage, e.g. 10.0)
    $this->btc->update([
        'price_change_24h' => 10.0,
    ]);

    // Set BTC wallet with 0.1 BTC (value = $6000.0)
    $btcWallet = Wallet::create([
        'user_id' => $this->user->id,
        'currency_id' => $this->btc->id,
        'balance' => 0.1,
        'address' => 'btc-addr-test',
        'status' => 'active',
    ]);

    // Set NGN wallet
    $ngnWallet = Wallet::create([
        'user_id' => $this->user->id,
        'currency_id' => $this->ngn->id,
        'balance' => 0,
        'address' => 'ngn-addr-test',
        'status' => 'active',
    ]);

    $this->marketDataMock->shouldReceive('getUsdNgnRate')->andReturn(1500.0);
    $this->marketDataMock->shouldReceive('getExchangeRate')->with($this->btc->id)->andReturn(60000.0);

    // Hit the dashboard endpoint
    $response = $this->actingAs($this->user)
        ->getJson('/api/dashboard');

    $response->assertStatus(200)
        ->assertJsonPath('data.success', true);

    // Let's check calculations:
    // BTC Balance USD = 0.1 * 60000.0 = 6000.0
    // Change Percentage = 10.0 / 100 = 0.1
    // PnL USD = 6000.0 * (0.1 / (1 + 0.1)) = 6000.0 * (0.1 / 1.1) = 545.45
    // PnL Percentage = (545.45 / (6000.0 - 545.45)) * 100 = (545.45 / 5454.55) * 100 = 10.00%
    $data = $response->json('data');

    $this->assertEquals(6000.0, $data['total_balance_usd']);
    $this->assertEquals(545.45, $data['pnl_24h_amount']);
    $this->assertEquals(10.00, $data['pnl_24h_percentage']);
});

test('it calculates single wallet PnL amount and percentage correctly when viewing wallet', function () {
    // 10% price change
    $this->btc->update([
        'price_change_24h' => 10.0,
    ]);

    // Set BTC wallet with 0.1 BTC (value = $6000.0)
    $btcWallet = Wallet::create([
        'user_id' => $this->user->id,
        'currency_id' => $this->btc->id,
        'balance' => 0.1,
        'address' => 'btc-addr-test',
        'status' => 'active',
    ]);

    $this->marketDataMock->shouldReceive('getUsdNgnRate')->andReturn(1500.0);
    $this->marketDataMock->shouldReceive('getExchangeRate')->with($this->btc->id)->andReturn(60000.0);

    // Hit the wallet detail endpoint
    $response = $this->actingAs($this->user)
        ->getJson("/api/wallets/wallet/{$this->btc->id}");

    $response->assertStatus(200);

    $walletData = $response->json('wallet');

    // BTC Balance USD = 0.1 * 60000.0 = 6000.0
    // PnL USD = 6000.0 * (0.1 / 1.1) = 545.45
    // PnL Percentage = 10.00%
    $this->assertEquals(6000.0, $walletData['balance_usd']);
    $this->assertEquals(545.45, $walletData['pnl_24h_amount']);
    $this->assertEquals(10.00, $walletData['pnl_24h_percentage']);
    $this->assertEquals(60000.0, $walletData['rate_usd']);
});
