<?php

use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\HdWallet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock the crypto gateway so no real HTTP calls are made
    $this->gatewayMock = Mockery::mock(CryptoGatewayInterface::class);
    app()->instance(CryptoGatewayInterface::class, $this->gatewayMock);
});

it('creates a fiat wallet for an authenticated user', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $ngn = Currency::create([
        'name'      => 'Nigerian Naira',
        'symbol'    => 'NGN',
        'is_active' => true,
        'is_crypto' => false,
    ]);

    $response = actingAs($user)->postJson('/api/wallets/create', [
        'currency_id' => $ngn->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('wallet.currency.symbol', 'NGN');
});

it('creates a crypto wallet by calling the gateway', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $btc = Currency::create([
        'name'      => 'Bitcoin',
        'symbol'    => 'BTC',
        'is_active' => true,
        'is_crypto' => true,
    ]);

    HdWallet::create([
        'currency_id'  => $btc->id,
        'signature_id' => 'test-sig-id',
        'xpub'         => 'test-xpub',
        'index'        => 0,
    ]);

    $this->gatewayMock
        ->shouldReceive('generateAddress')
        ->once()
        ->with($btc->id)
        ->andReturn(['address' => 'btc-test-address']);

    $response = actingAs($user)->postJson('/api/wallets/create', [
        'currency_id' => $btc->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('wallet.address', 'btc-test-address')
        ->assertJsonPath('wallet.currency.symbol', 'BTC');
});

it('prevents creating a duplicate wallet for the same currency', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $ngn = Currency::create([
        'name'      => 'Nigerian Naira',
        'symbol'    => 'NGN',
        'is_active' => true,
        'is_crypto' => false,
    ]);

    // Create one wallet first
    $user->wallets()->create(['currency_id' => $ngn->id, 'address' => 'existing', 'status' => 'active']);

    $response = actingAs($user)->postJson('/api/wallets/create', [
        'currency_id' => $ngn->id,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['currency_id']);
});

it('returns 422 for an invalid currency_id', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $response = actingAs($user)->postJson('/api/wallets/create', [
        'currency_id' => 99999,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['currency_id']);
});

it('returns 401 for unauthenticated requests', function () {
    postJson('/api/wallets/create', ['currency_id' => 1])->assertStatus(401);
});
