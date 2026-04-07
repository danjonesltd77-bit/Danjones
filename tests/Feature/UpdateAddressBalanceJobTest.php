<?php

namespace Tests\Feature;

use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Jobs\UpdateAddressBalanceJob;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\Wallet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

uses(RefreshDatabase::class);

test('UpdateAddressBalanceJob updates a single wallet balance', function () {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['name' => 'Bitcoin', 'is_crypto' => true]);
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
        'currency_id' => $currency->id,
        'address' => 'tb1q9j4c...',
        'address_balance' => 0.0,
    ]);

    $mockGateway = Mockery::mock(CryptoGatewayInterface::class);
    $mockGateway->shouldReceive('getBalance')
        ->once()
        ->with($wallet->address, Mockery::type(Currency::class))
        ->andReturn(0.5);

    $job = new UpdateAddressBalanceJob($wallet);
    $job->handle($mockGateway);

    expect($wallet->fresh()->address_balance)->toBe(0.5);
});

test('UpdateAddressBalanceJob updates all wallets when no wallet is passed', function () {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['name' => 'Bitcoin', 'is_crypto' => true]);

    $wallet1 = Wallet::factory()->create([
        'user_id' => $user->id,
        'currency_id' => $currency->id,
        'address' => 'addr1',
        'address_balance' => 0.0,
    ]);

    $wallet2 = Wallet::factory()->create([
        'user_id' => $user->id,
        'currency_id' => $currency->id,
        'address' => 'addr2',
        'address_balance' => 0.0,
    ]);

    $mockGateway = Mockery::mock(CryptoGatewayInterface::class);
    $mockGateway->shouldReceive('getBalance')
        ->twice()
        ->andReturn(1.0);

    $job = new UpdateAddressBalanceJob;
    $job->handle($mockGateway);

    expect((float) $wallet1->fresh()->address_balance)->toBe(1.0);
    expect((float) $wallet2->fresh()->address_balance)->toBe(1.0);
});
