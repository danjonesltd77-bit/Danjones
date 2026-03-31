<?php

namespace Tests\Feature\Domains\Wallet\Api;

use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\Wallet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->marketDataMock = Mockery::mock(MarketDataGatewayInterface::class);
    $this->app->instance(MarketDataGatewayInterface::class, $this->marketDataMock);
});

it('returns the authenticated user\'s wallet rates and global USD/NGN rate', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $btc = Currency::create([
        'id' => 2,
        'name' => 'Bitcoin',
        'symbol' => 'BTC',
        'is_crypto' => true,
        'is_active' => true,
        'decimal' => 8
    ]);

    $naira = Currency::create([
        'id' => 1,
        'name' => 'Naira',
        'symbol' => 'NGN',
        'is_crypto' => false,
        'is_active' => true,
        'decimal' => 2
    ]);

    $wallet = Wallet::create([
        'user_id' => $user->id,
        'currency_id' => $btc->id,
        'address' => 'btc-addr',
        'balance' => 0.5,
        'status' => 'active'
    ]);

    $nairaWallet = Wallet::create([
        'user_id' => $user->id,
        'currency_id' => $naira->id,
        'address' => 'NGN-addr',
        'balance' => 1000.0,
        'status' => 'active'
    ]);

    $this->marketDataMock->shouldReceive('getUsdNgnRate')->andReturn(1500.0);
    $this->marketDataMock->shouldReceive('getExchangeRate')->with($btc->id)->andReturn(60000.0);

    $response = actingAs($user)->getJson('/api/wallets/rates');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'wallets')
        ->assertJsonMissing(['symbol' => 'NGN'])
        ->assertJsonStructure([
            'usd_ngn_rate',
            'wallets' => [
                '*' => [
                    'currency_id',
                    'symbol',
                    'name',
                    'balance',
                    'rate_usd',
                    'balance_usd',
                ]
            ]
        ])
        ->assertJsonPath('usd_ngn_rate', 1500)
        ->assertJsonFragment([
            'symbol' => 'BTC',
            'balance' => 0.5,
            'rate_usd' => 60000.0,
            'balance_usd' => 30000.0,
        ]);
});
