<?php

use App\Models\User;
use App\Domains\Wallet\Models\Wallet;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock the MarketDataGateway
    $this->marketDataMock = Mockery::mock(MarketDataGatewayInterface::class);
    $this->app->instance(MarketDataGatewayInterface::class, $this->marketDataMock);

    // Create NGN currency
    $this->ngn = Currency::factory()->create([
        'id' => 1,
        'name' => 'Naira',
        'symbol' => 'NGN',
        'is_crypto' => false,
        'is_active' => true,
    ]);

    // Create BTC currency
    $this->btc = Currency::factory()->create([
        'id' => 2,
        'name' => 'Bitcoin',
        'symbol' => 'BTC',
        'is_crypto' => true,
        'is_active' => true,
    ]);
});

test('it returns dashboard data for authenticated user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // Create wallets
    Wallet::factory()->create([
        'user_id' => $user->id,
        'currency_id' => $this->ngn->id,
        'balance' => 1500,
    ]);

    Wallet::factory()->create([
        'user_id' => $user->id,
        'currency_id' => $this->btc->id,
        'balance' => 1,
    ]);

    // Mock rates
    $this->marketDataMock->shouldReceive('getExchangeRate')
        ->with($this->btc->id)
        ->andReturn(60000.0); // 1 BTC = $60,000

    $this->marketDataMock->shouldReceive('getUsdNgnRate')
        ->andReturn(1500.0); // $1 = 1500 NGN

    $response = $this->getJson('/api/dashboard');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'success',
                'user',
                'wallets',
                'total_balance_usd',
                'total_balance_ngn',
                'coins'
            ]
        ]);

    // Calculations:
    // NGN wallet: 1500 NGN / 1500 (rate) = $1
    // BTC wallet: 1 BTC * 60000 = $60000
    // Total USD = $60001
    // Total NGN = 60001 * 1500 = 90,001,500
    
    $response->assertJsonPath('data.total_balance_usd', 60001);
    $response->assertJsonPath('data.total_balance_ngn', 90001500);
});

test('it requires authentication for dashboard', function () {
    $response = $this->getJson('/api/dashboard');
    $response->assertStatus(401);
});
