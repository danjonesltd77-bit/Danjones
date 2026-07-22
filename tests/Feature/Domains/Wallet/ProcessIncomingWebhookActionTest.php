<?php

use App\Domains\Wallet\Actions\ProcessIncomingWebhookAction;
use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\Wallet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('process incoming webhook action correctly resolves fungible token currency via contractAddress', function () {
    Mail::fake();

    $user = User::factory()->create();

    $tronCurrency = Currency::factory()->create([
        'id' => 3,
        'name' => 'Tron',
        'symbol' => 'TRX',
        'is_crypto' => true,
        'is_active' => true,
        'token_currency' => 'TRON',
        'token_address' => '0',
    ]);

    $usdtCurrency = Currency::factory()->create([
        'id' => 4,
        'parent_id' => $tronCurrency->id,
        'name' => 'USDT',
        'symbol' => 'USDT',
        'is_crypto' => true,
        'is_active' => true,
        'token_currency' => 'USDT_TRON',
        'token_address' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
    ]);

    $walletUsdt = Wallet::factory()->create([
        'user_id' => $user->id,
        'currency_id' => $usdtCurrency->id,
        'address' => 'TL1vj3LGtnL77KUA2cmwKRHP8gghjwLQy1',
        'balance' => 0,
    ]);

    $cryptoGatewayMock = Mockery::mock(CryptoGatewayInterface::class);
    $cryptoGatewayMock->shouldReceive('getTransactionDetails')
        ->once()
        ->with('75746cc8d54429df9c63531330740a80c323dc90318621f4e707f13da230fd6d', Mockery::on(fn ($c) => $c->id === $usdtCurrency->id))
        ->andReturn([
            'blockNumber' => 84677179,
            'log' => [
                [
                    'topics' => [
                        'ddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef',
                        '000000000000000000000000c686c48436aec3a1dfce4ac5a4526c39366985ba',
                        '0000000000000000000000006e34eb477452685a68137e25e45f80f89d8b09dd',
                    ],
                    'data' => '00000000000000000000000000000000000000000000000000000000004c4b40',
                ],
            ],
        ]);

    $marketDataGatewayMock = Mockery::mock(MarketDataGatewayInterface::class);
    $marketDataGatewayMock->shouldReceive('getExchangeRate')
        ->once()
        ->with($usdtCurrency->id)
        ->andReturn(1.0);

    $action = new ProcessIncomingWebhookAction($cryptoGatewayMock, $marketDataGatewayMock);

    $payload = [
        'chain' => 'tron-mainnet',
        'address' => 'TL1vj3LGtnL77KUA2cmwKRHP8gghjwLQy1',
        'counterAddress' => 'TU4vEruvZwLLkSfV9bNw12EJTPvNr7Pvaa',
        'amount' => '5',
        'currency' => 'TRON',
        'contractAddress' => 'USDT_TRON',
        'subscriptionId' => '6a25b7b54dd0946d4fcad282',
        'subscriptionType' => 'INCOMING_FUNGIBLE_TX',
        'txId' => '75746cc8d54429df9c63531330740a80c323dc90318621f4e707f13da230fd6d',
        'blockNumber' => 84677179,
        'timestamp' => 1784702013430,
    ];

    $result = $action->execute($payload);

    expect($result['success'])->toBeTrue()
        ->and($result['amount'])->toBe(5.0);

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'currency_id' => $usdtCurrency->id,
        'reference' => '75746cc8d54429df9c63531330740a80c323dc90318621f4e707f13da230fd6d',
        'action' => 'deposit',
        'status' => 'completed',
    ]);

    Mail::assertQueued(\App\Mail\Wallet\DepositReceivedMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});
