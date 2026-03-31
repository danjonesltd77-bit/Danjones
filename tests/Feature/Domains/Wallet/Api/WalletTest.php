<?php

use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\Wallet;
use App\Models\User;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->marketDataMock = Mockery::mock(MarketDataGatewayInterface::class);
    $this->app->instance(MarketDataGatewayInterface::class, $this->marketDataMock);
});

// ── Currencies ────────────────────────────────────────────────────────────────

it('returns all active currencies for an authenticated user', function () {
    /** @var User $user */
    $user = User::factory()->create();

    Currency::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'is_active' => true]);
    Currency::create(['name' => 'Ethereum', 'symbol' => 'ETH', 'is_active' => true]);
    Currency::create(['name' => 'Inactive Coin', 'symbol' => 'INC', 'is_active' => false]);

    $response = actingAs($user)->getJson('/api/wallets/currencies');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'currencies')
        ->assertJsonFragment(['symbol' => 'BTC'])
        ->assertJsonFragment(['symbol' => 'ETH'])
        ->assertJsonMissing(['symbol' => 'INC']);
});

it('returns 401 for unauthenticated requests to currencies', function () {
    getJson('/api/wallets/currencies')->assertStatus(401);
});

// ── Wallets ───────────────────────────────────────────────────────────────────

it('returns the authenticated user\'s wallets with currency data and USD balance', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $other = User::factory()->create();

    $btc = Currency::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'is_active' => true, 'is_crypto' => true, 'decimal' => 8]);
    $eth = Currency::create(['name' => 'Ethereum', 'symbol' => 'ETH', 'is_active' => true, 'is_crypto' => true, 'decimal' => 8]);
    $sol = Currency::create(['name' => 'Solana', 'symbol' => 'SOL', 'is_active' => true, 'is_crypto' => true, 'decimal' => 9]);

    Wallet::create(['user_id' => $user->id, 'currency_id' => $btc->id, 'address' => 'btc-addr', 'balance' => 0.5, 'status' => 'active']);
    Wallet::create(['user_id' => $user->id, 'currency_id' => $eth->id, 'address' => 'eth-addr', 'balance' => 1.0, 'status' => 'active']);
    Wallet::create(['user_id' => $other->id, 'currency_id' => $btc->id, 'address' => 'other-addr', 'balance' => 1.0, 'status' => 'active']);

    $this->marketDataMock->shouldReceive('getExchangeRate')->with($btc->id)->andReturn(60000.0);
    $this->marketDataMock->shouldReceive('getExchangeRate')->with($eth->id)->andReturn(3000.0);
    $this->marketDataMock->shouldReceive('getUsdNgnRate')->andReturn(1500.0);

    $response = actingAs($user)->getJson('/api/wallets');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'wallets')
        ->assertJsonCount(1, 'available_currencies')
        ->assertJsonFragment(['address' => 'btc-addr', 'balance_usd' => 30000.0])
        ->assertJsonFragment(['address' => 'eth-addr', 'balance_usd' => 3000.0])
        ->assertJsonFragment(['symbol' => 'SOL'])
        ->assertJsonMissing(['address' => 'other-addr']);

    // Ensure each wallet includes nested currency data
    $wallets = $response->json('wallets');
    expect($wallets[0])->toHaveKey('currency');

    // Ensure available_currencies contains the correct data
    $availableCurrencies = $response->json('available_currencies');
    expect($availableCurrencies[0]['symbol'])->toBe('SOL');
});

it('returns 401 for unauthenticated requests to wallets', function () {
    getJson('/api/wallets')->assertStatus(401);
});
