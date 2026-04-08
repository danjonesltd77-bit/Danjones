<?php

namespace Tests\Feature;

use App\Domains\Core\Services\SettingService;
use App\Domains\Wallet\Actions\SendOnchainAction;
use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\HdWallet;
use App\Domains\Wallet\Models\OnchainSend;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Models\Wallet;
use App\Enum\SystemWalletType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

uses(RefreshDatabase::class);

test('SendOnchainAction selects wallets and broadcasts correctly', function () {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['id' => 2, 'name' => 'Bitcoin', 'symbol' => 'BTC', 'is_crypto' => true]);
    $hdWallet = HdWallet::factory()->create([
        'currency_id' => $currency->id,
        'signature_id' => 'sig_123',
        'xpub' => 'xpub_123',
    ]);

    $wallet1 = Wallet::factory()->create([
        'user_id' => $user->id,
        'currency_id' => $currency->id,
        'address_balance' => 1.0,
        'address' => 'addr1',
        'index' => 1,
    ]);

    $changeWallet = SystemWallet::factory()->create([
        'currency_id' => $currency->id,
        'type' => SystemWalletType::CHANGE,
        'address' => 'change_addr',
    ]);

    $mockGateway = Mockery::mock(CryptoGatewayInterface::class);
    $mockMarketData = Mockery::mock(MarketDataGatewayInterface::class);
    $mockSettings = Mockery::mock(SettingService::class);

    // Mock balance check for change wallet
    $mockGateway->shouldReceive('getBalance')
        ->once()
        ->with('change_addr', Mockery::any())
        ->andReturn(0.5);

    // Mock fee estimation
    $mockGateway->shouldReceive('estimateOnchainFee')
        ->once()
        ->with(Mockery::type(Currency::class), 0.1)
        ->andReturn(0.0001);

    // Mock market rate
    $mockMarketData->shouldReceive('getExchangeRate')->andReturn(60000.0);

    // Mock settings
    $mockSettings->shouldReceive('get')->with('onchain_fee_max_usd', 50.0)->andReturn(50.0);

    // Mock broadcast
    $mockGateway->shouldReceive('utxoSend')
        ->once()
        ->with(
            Mockery::type(Currency::class),
            Mockery::type('array'),
            'recipient_addr',
            0.1,
            0.0001,
            'change_addr'
        )
        ->andReturn(['signatureId' => 'final_sig', 'txId' => 'tx_123']);

    $action = new SendOnchainAction($mockGateway, $mockMarketData, $mockSettings);
    $result = $action->execute(0.1, 2, 'recipient_addr');

    expect($result['txId'])->toBe('tx_123');
    expect(OnchainSend::count())->toBe(1);
    expect((float) $wallet1->fresh()->address_balance)->toBe(0.0); // Selected wallet zeroed out
});
