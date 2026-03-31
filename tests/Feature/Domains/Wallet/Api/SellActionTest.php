<?php

namespace Tests\Feature\Domains\Wallet\Api;

use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Models\Wallet;
use App\Enum\SystemWalletType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed currencies
    $this->naira = Currency::create([
        'id' => 1,
        'name' => 'Naira',
        'symbol' => 'NGN',
        'is_crypto' => false,
        'is_active' => true,
        'decimal' => 2,
    ]);

    $this->bitcoin = Currency::create([
        'id' => 2,
        'name' => 'Bitcoin',
        'symbol' => 'BTC',
        'is_crypto' => true,
        'is_active' => true,
        'decimal' => 8,
        'token_currency' => 'BTC',
        'token_address' => '0x...',
        'token_id' => '1',
        'contract_type' => 0,
        'fee' => 0.001,
    ]);

    \App\Domains\Wallet\Models\HdWallet::create([
        'currency_id' => $this->bitcoin->id,
        'xpub' => 'xpub...',
        'private_key' => 'priv...',
        'signature_id' => 'sig-id',
        'index' => 0,
    ]);

    $this->user = User::factory()->create();

    // Create wallets
    $this->btcWallet = Wallet::create([
        'user_id' => $this->user->id,
        'currency_id' => $this->bitcoin->id,
        'address' => 'btc-test-address',
        'balance' => 0.5,
        'status' => \App\Enum\WalletStatus::ACTIVE,
    ]);

    $this->nairaWallet = Wallet::create([
        'user_id' => $this->user->id,
        'currency_id' => $this->naira->id,
        'address' => 'naira-test-address',
        'balance' => 0.0,
        'status' => \App\Enum\WalletStatus::ACTIVE,
    ]);

    SystemWallet::create([
        'type' => SystemWalletType::SELL,
        'currency_id' => $this->bitcoin->id,
        'address' => 'sys-btc-sell-address',
        'balance' => 0.0,
    ]);

    SystemWallet::create([
        'type' => SystemWalletType::FEE,
        'currency_id' => $this->bitcoin->id,
        'address' => 'sys-btc-fee-address',
        'balance' => 0.0,
    ]);

    SystemWallet::create([
        'type' => SystemWalletType::GAS,
        'currency_id' => $this->bitcoin->id,
        'address' => 'sys-btc-gas-address',
        'balance' => 10.0,
    ]);

    \App\Domains\Core\Models\Setting::create([
        'key' => 'sell_fee_percentage',
        'value' => '1.5',
        'type' => 'float',
    ]);

    \App\Domains\Core\Models\Setting::create([
        'key' => 'usd_ngn_rate',
        'value' => '1500',
        'type' => 'float',
    ]);
});

it('allows a user to sell crypto for NGN', function () {
    $sellAmount = 0.1;

    // Mock MarketDataGatewayInterface
    $mockGateway = mock(\App\Domains\Wallet\Contracts\MarketDataGatewayInterface::class);
    $mockGateway->shouldReceive('getExchangeRate')->andReturn(50000.0);
    $mockGateway->shouldReceive('getUsdNgnRate')->andReturn(1500.0);
    app()->instance(\App\Domains\Wallet\Contracts\MarketDataGatewayInterface::class, $mockGateway);

    $response = actingAs($this->user)->postJson('/api/wallets/sell', [
        'currency_id' => $this->bitcoin->id,
        'amount' => $sellAmount,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    $this->btcWallet->refresh();
    $this->nairaWallet->refresh();

    expect($this->btcWallet->balance)->toBe(0.4);
    expect($this->nairaWallet->balance)->toBeGreaterThan(0);
});

it('prevents selling with insufficient balance', function () {
    $response = actingAs($this->user)->postJson('/api/wallets/sell', [
        'currency_id' => $this->bitcoin->id,
        'amount' => 1.0, // User only has 0.5
    ]);

    $response->assertStatus(400);
});

it('prevents selling from a non-existent wallet', function () {
    $eth = Currency::create(['name' => 'Ethereum', 'symbol' => 'ETH', 'is_active' => true]);

    $response = actingAs($this->user)->postJson('/api/wallets/sell', [
        'currency_id' => $eth->id,
        'amount' => 0.1,
    ]);

    $response->assertStatus(404);
});

it('triggers on-chain transfer for gaspump currencies', function () {
    // Setup a gaspump currency
    $this->bitcoin->update(['is_gaspump' => true]);

    $sellAmount = 0.1;
    $mockSignatureId = 'mock-signature-id';

    // Mock MarketDataGatewayInterface
    $mockGateway = mock(\App\Domains\Wallet\Contracts\MarketDataGatewayInterface::class);
    $mockGateway->shouldReceive('getExchangeRate')->andReturn(50000.0);
    $mockGateway->shouldReceive('getUsdNgnRate')->andReturn(1500.0);
    app()->instance(\App\Domains\Wallet\Contracts\MarketDataGatewayInterface::class, $mockGateway);

    // Mock GaspumpServiceInterface
    $mockGaspump = mock(\App\Domains\Wallet\Contracts\GaspumpServiceInterface::class);
    $mockGaspump->shouldReceive('multipleTransfer')->once()->andReturn($mockSignatureId);
    app()->instance(\App\Domains\Wallet\Contracts\GaspumpServiceInterface::class, $mockGaspump);

    $response = actingAs($this->user)->postJson('/api/wallets/sell', [
        'currency_id' => $this->bitcoin->id,
        'amount' => $sellAmount,
    ]);

    $response->assertStatus(200);

    // Verify metadata in database
    $this->assertDatabaseHas('transactions', [
        'wallet_id' => $this->btcWallet->id,
        'action' => 'sell',
        'metadata' => json_encode([
            'crypto_usd_rate' => 50000.0,
            'usd_ngn_rate' => 1500.0,
            'fee_percentage' => 1.5,
            'signatureId' => $mockSignatureId,
        ]),
    ]);
});

it('activates pending gaspump wallet before allowing sale', function () {
    $this->bitcoin->update(['is_gaspump' => true]);
    $this->btcWallet->update(['status' => \App\Enum\WalletStatus::PENDING]);

    $sellAmount = 0.1;

    // Mock MarketDataGatewayInterface
    $mockGateway = mock(\App\Domains\Wallet\Contracts\MarketDataGatewayInterface::class);
    $mockGateway->shouldReceive('getExchangeRate')->andReturn(50000.0);
    $mockGateway->shouldReceive('getUsdNgnRate')->andReturn(1500.0);
    app()->instance(\App\Domains\Wallet\Contracts\MarketDataGatewayInterface::class, $mockGateway);

    // Mock GaspumpServiceInterface
    $mockGaspump = mock(\App\Domains\Wallet\Contracts\GaspumpServiceInterface::class);
    $mockGaspump->shouldReceive('activateAddress')->once()->andReturn('mock-activation-sig');
    app()->instance(\App\Domains\Wallet\Contracts\GaspumpServiceInterface::class, $mockGaspump);

    $response = actingAs($this->user)->postJson('/api/wallets/sell', [
        'currency_id' => $this->bitcoin->id,
        'amount' => $sellAmount,
    ]);

    $response->assertStatus(400)
        ->assertJsonPath('message', 'Wallet not activated, please retry in 5 minutes');

    $this->btcWallet->refresh();
    expect($this->btcWallet->status)->toBe(\App\Enum\WalletStatus::ACTIVE);

    // Balance should remain unchanged as transaction was aborted after activation
    expect($this->btcWallet->balance)->toBe(0.5);
});
