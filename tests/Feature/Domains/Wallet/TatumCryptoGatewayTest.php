<?php

use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\HdWallet;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Models\Wallet;
use App\Enum\SystemWalletType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('utxoSend formats fee as string with 8 decimal places in payload', function () {
    $currency = Currency::factory()->create([
        'name' => 'Bitcoin',
        'symbol' => 'BTC',
        'decimal' => 8,
        'is_crypto' => true,
        'is_active' => true,
        'is_gaspump' => false,
    ]);

    $hdWallet = HdWallet::factory()->create([
        'currency_id' => $currency->id,
        'signature_id' => 'mock-sig-id',
    ]);
    $currency->setRelation('hdWallet', $hdWallet);

    $changeWallet = SystemWallet::factory()->create([
        'currency_id' => $currency->id,
        'type' => SystemWalletType::CHANGE,
        'address' => 'change-address',
    ]);

    $wallet = Wallet::factory()->create([
        'currency_id' => $currency->id,
        'address' => 'user-address',
        'address_balance' => 1.5,
        'index' => 1,
    ]);

    Http::fake([
        '*/bitcoin/address/balance/change-address' => Http::response([
            'incoming' => '0.5',
            'outgoing' => '0.0',
        ]),
        '*/bitcoin/transaction' => function (\Illuminate\Http\Client\Request $request) {
            $data = $request->data();
            expect($data['fee'])->toBe('0.00012345');

            return Http::response([
                'txId' => 'mock-tx-id',
            ], 200);
        },
    ]);

    $gateway = app(CryptoGatewayInterface::class);

    $result = $gateway->utxoSend($currency, 'recipient-address', 0.8, 0.00012345);

    expect($result)->toBeArray()
        ->and($result['txId'])->toBe('mock-tx-id');
});
