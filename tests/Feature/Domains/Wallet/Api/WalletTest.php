<?php

use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\Wallet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

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

it('returns the authenticated user\'s wallets with currency data', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $other = User::factory()->create();

    $btc = Currency::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'is_active' => true]);
    $eth = Currency::create(['name' => 'Ethereum', 'symbol' => 'ETH', 'is_active' => true]);

    Wallet::create(['user_id' => $user->id, 'currency_id' => $btc->id, 'address' => 'btc-addr', 'status' => 'active']);
    Wallet::create(['user_id' => $user->id, 'currency_id' => $eth->id, 'address' => 'eth-addr', 'status' => 'active']);
    Wallet::create(['user_id' => $other->id, 'currency_id' => $btc->id, 'address' => 'other-addr', 'status' => 'active']);

    $response = actingAs($user)->getJson('/api/wallets');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'wallets')
        ->assertJsonFragment(['address' => 'btc-addr'])
        ->assertJsonFragment(['address' => 'eth-addr'])
        ->assertJsonMissing(['address' => 'other-addr']);

    // Ensure each wallet includes nested currency data
    $wallets = $response->json('wallets');
    expect($wallets[0])->toHaveKey('currency');
});

it('returns 401 for unauthenticated requests to wallets', function () {
    getJson('/api/wallets')->assertStatus(401);
});
