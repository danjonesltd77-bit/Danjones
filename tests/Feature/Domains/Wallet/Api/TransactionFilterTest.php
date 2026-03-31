<?php

namespace Tests\Feature\Domains\Wallet\Api;

use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\Transaction;
use App\Domains\Wallet\Models\Wallet;
use App\Enum\TransactionAction;
use App\Enum\WalletStatus;
use App\Models\User;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->marketDataMock = Mockery::mock(MarketDataGatewayInterface::class);
    $this->app->instance(MarketDataGatewayInterface::class, $this->marketDataMock);
    $this->marketDataMock->shouldReceive('getUsdNgnRate')->andReturn(1500.0);
});

it('excludes fee transactions from the wallet details response', function () {
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

    $wallet = Wallet::create([
        'user_id' => $user->id,
        'currency_id' => $btc->id,
        'address' => 'btc-addr',
        'balance' => 1.0,
        'status' => WalletStatus::ACTIVE
    ]);

    // Create a normal transaction
    Transaction::create([
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'wallet_type' => Wallet::class,
        'currency_id' => $btc->id,
        'action' => TransactionAction::DEPOSIT->value,
        'amount' => 1.0,
        'usd' => 60000.0,
        'type' => 'credit',
        'previous_balance' => 0.0,
        'current_balance' => 1.0,
        'reference' => 'TX1',
        'status' => 'completed'
    ]);

    // Create a fee transaction
    Transaction::create([
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'wallet_type' => Wallet::class,
        'currency_id' => $btc->id,
        'action' => TransactionAction::FEE->value,
        'amount' => 0.01,
        'usd' => 600.0,
        'type' => 'debit',
        'previous_balance' => 1.0,
        'current_balance' => 0.99,
        'reference' => 'TX2',
        'status' => 'completed'
    ]);

    $this->marketDataMock->shouldReceive('getExchangeRate')->with($btc->id)->andReturn(60000.0);

    $response = actingAs($user)->getJson("/api/wallets/wallet/{$btc->id}");

    $response->assertStatus(200);
    
    $transactions = $response->json('wallet.transactions');
    expect($transactions)->toHaveCount(1);
    expect($transactions[0]['action'])->toBe(TransactionAction::DEPOSIT->value);
    
    // Ensure fee transaction is missing
    $actions = collect($transactions)->pluck('action');
    expect($actions)->not->toContain(TransactionAction::FEE->value);
});
