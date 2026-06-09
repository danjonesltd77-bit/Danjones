<?php

use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Contracts\SupportsWebhooksInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\HdWallet;
use App\Domains\Wallet\Models\Wallet;
use App\Enum\WalletStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creating a child wallet automatically creates parent wallet if not exists', function () {
    // 1. Create a parent currency (e.g. Ethereum)
    $parentCurrency = Currency::factory()->create([
        'symbol' => 'ETH',
        'name' => 'Ethereum',
        'is_crypto' => true,
        'parent_id' => null,
    ]);

    // 2. Create child currency (e.g. USDT on Ethereum)
    $childCurrency = Currency::factory()->create([
        'symbol' => 'USDT',
        'name' => 'USDT (ERC-20)',
        'is_crypto' => true,
        'parent_id' => $parentCurrency->id,
    ]);

    // Create HD Wallets for both currencies
    HdWallet::create([
        'currency_id' => $parentCurrency->id,
        'xpub' => 'xpub-eth',
        'private_key' => 'priv-eth',
        'signature_id' => 'sig-eth',
        'index' => 1,
    ]);

    HdWallet::create([
        'currency_id' => $childCurrency->id,
        'xpub' => 'xpub-usdt',
        'private_key' => 'priv-usdt',
        'signature_id' => 'sig-usdt',
        'index' => 1,
    ]);

    // Mock the crypto gateway to return a generated address for the parent currency
    $gatewayMock = Mockery::mock(CryptoGatewayInterface::class, SupportsWebhooksInterface::class);
    $gatewayMock->shouldReceive('generateAddress')
        ->once()
        ->andReturn('generated-ethereum-address');

    $gatewayMock->shouldReceive('subscribeToIncoming')
        ->twice() // Twice because both ETH and USDT will be subscribed!
        ->andReturn(true);

    $this->app->instance(CryptoGatewayInterface::class, $gatewayMock);

    $user = User::factory()->create();

    // Verify wallets are empty initially
    expect($user->wallets)->toBeEmpty();

    // Call POST /api/wallets/create for child currency
    $response = $this->actingAs($user)
        ->postJson('/api/wallets/create', [
            'currency_id' => $childCurrency->id,
        ]);

    $response->assertStatus(201);

    // Verify both child and parent wallets were created
    $user->refresh();
    expect($user->wallets)->toHaveCount(2);

    $parentWallet = $user->wallets()->where('currency_id', $parentCurrency->id)->first();
    $childWallet = $user->wallets()->where('currency_id', $childCurrency->id)->first();

    expect($parentWallet)->not->toBeNull();
    expect($childWallet)->not->toBeNull();

    // Verify they share the same address
    expect($parentWallet->address)->toEqual('generated-ethereum-address');
    expect($childWallet->address)->toEqual('generated-ethereum-address');
});

test('creating a child wallet reuses parent wallet address if parent exists', function () {
    $parentCurrency = Currency::factory()->create([
        'symbol' => 'ETH',
        'name' => 'Ethereum',
        'is_crypto' => true,
        'parent_id' => null,
    ]);

    $childCurrency = Currency::factory()->create([
        'symbol' => 'USDT',
        'name' => 'USDT (ERC-20)',
        'is_crypto' => true,
        'parent_id' => $parentCurrency->id,
    ]);

    HdWallet::create([
        'currency_id' => $parentCurrency->id,
        'xpub' => 'xpub-eth',
        'private_key' => 'priv-eth',
        'signature_id' => 'sig-eth',
        'index' => 1,
    ]);

    HdWallet::create([
        'currency_id' => $childCurrency->id,
        'xpub' => 'xpub-usdt',
        'private_key' => 'priv-usdt',
        'signature_id' => 'sig-usdt',
        'index' => 1,
    ]);

    $user = User::factory()->create();

    // Pre-create the parent wallet
    $parentWallet = Wallet::create([
        'user_id' => $user->id,
        'currency_id' => $parentCurrency->id,
        'address' => 'existing-ethereum-address',
        'index' => 1,
        'status' => WalletStatus::ACTIVE,
    ]);

    // Mock the crypto gateway - should NOT call generateAddress since parent wallet already exists
    $gatewayMock = Mockery::mock(CryptoGatewayInterface::class, SupportsWebhooksInterface::class);
    $gatewayMock->shouldNotReceive('generateAddress');
    $gatewayMock->shouldReceive('subscribeToIncoming')
        ->once() // Only for child wallet
        ->andReturn(true);

    $this->app->instance(CryptoGatewayInterface::class, $gatewayMock);

    // Call POST /api/wallets/create for child currency
    $response = $this->actingAs($user)
        ->postJson('/api/wallets/create', [
            'currency_id' => $childCurrency->id,
        ]);

    $response->assertStatus(201);

    // Verify only the child wallet was added
    $user->refresh();
    expect($user->wallets)->toHaveCount(2);

    $childWallet = $user->wallets()->where('currency_id', $childCurrency->id)->first();
    expect($childWallet->address)->toEqual('existing-ethereum-address');
});
