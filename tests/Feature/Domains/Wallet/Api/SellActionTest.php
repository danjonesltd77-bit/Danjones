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
    ]);

    $this->user = User::factory()->create();

    // Create wallets
    $this->btcWallet = Wallet::create([
        'user_id' => $this->user->id,
        'currency_id' => $this->bitcoin->id,
        'address' => 'btc-test-address',
        'balance' => 0.5,
        'status' => 'active',
    ]);

    $this->nairaWallet = Wallet::create([
        'user_id' => $this->user->id,
        'currency_id' => $this->naira->id,
        'address' => 'naira-test-address',
        'balance' => 0.0,
        'status' => 'active',
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
    // Rate in TatumCryptoGateway is set to 1500 (USD/NGN) currently.
    // BTC/USD rate from TatumCryptoGateway depends on mock or real API.
    // Since we are not mocking the gateway yet, it will try to call the API.
    // We should probably mock the MarketDataGatewayInterface for this test.

    $sellAmount = 0.1;

    // We expect:
    // BTC balance: 0.5 - 0.1 = 0.4
    // If BTC/USD is e.g. 50,000, then 0.1 BTC = 5,000 USD
    // If USD/NGN is 1500, then 5,000 USD = 7,500,000 NGN

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

    // Verify ledger entries
    $expectedFee = $sellAmount * (1.5 / 100); // 1.5% fee
    $expectedNet = $sellAmount - $expectedFee;

    $this->assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'wallet_id' => $this->btcWallet->id,
        'action' => 'withdrawal',
        'type' => 'debit',
        'amount' => $expectedNet,
    ]);

    $this->assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'wallet_id' => $this->btcWallet->id,
        'action' => 'fee',
        'type' => 'debit',
        'amount' => $expectedFee,
    ]);

    $this->assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'wallet_id' => $this->nairaWallet->id,
        'action' => 'deposit',
        'type' => 'credit',
    ]);
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
