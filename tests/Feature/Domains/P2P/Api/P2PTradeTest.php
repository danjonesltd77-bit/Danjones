<?php

namespace Tests\Feature\Domains\P2P\Api;

use App\Domains\P2P\Models\P2PAdvertisement;
use App\Domains\P2P\Models\P2PTrade;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\HdWallet;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Models\Wallet;
use App\Enum\AdvertisementType;
use App\Enum\SystemWalletType;
use App\Enum\TradeStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite') {
        \Illuminate\Support\Facades\DB::connection()->getPdo()->sqliteCreateFunction('UNIX_TIMESTAMP', function ($val) {
            return $val ? strtotime($val) : null;
        });
    }

    $this->seller = User::factory()->create();
    $this->buyer = User::factory()->create();

    $this->currency = Currency::factory()->create();

    // Seller's crypto wallet
    $this->sellerWallet = Wallet::create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
        'address' => 'seller-address',
        'balance' => 10.0,
        'status' => 'active',
    ]);

    // System Escrow Wallet
    SystemWallet::create([
        'type' => SystemWalletType::ESCROW,
        'currency_id' => $this->currency->id,
        'address' => 'sys-escrow',
        'balance' => 0.0,
    ]);

    // Create a Bank
    $this->bank = \App\Domains\Bank\Models\Bank::create([
        'name' => 'Test Bank',
        'code' => 'TEST_BANK',
    ]);

    // Create Seller's Bank Account
    $this->sellerBankAccount = \App\Domains\Bank\Models\BankAccount::create([
        'user_id' => $this->seller->id,
        'bank_id' => $this->bank->id,
        'account_name' => 'Seller Account',
        'account_number' => '1234567890',
        'is_active' => true,
    ]);

    // Create Buyer's Bank Account
    $this->buyerBankAccount = \App\Domains\Bank\Models\BankAccount::create([
        'user_id' => $this->buyer->id,
        'bank_id' => $this->bank->id,
        'account_name' => 'Buyer Account',
        'account_number' => '0987654321',
        'is_active' => true,
    ]);
});

it('can create a sell advertisement if you have sufficient crypto', function () {
    $response = actingAs($this->seller)->postJson('/api/p2p/create-ads', [
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::SELL->value,
        'price' => 50000,
        'total_amount' => 5.0,
        'min_limit' => 5000,
        'max_limit' => 250000,
        'terms' => 'Fast payment only',
        'bank_account_id' => $this->sellerBankAccount->id,
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('p2p_advertisements', [
        'user_id' => $this->seller->id,
        'total_amount' => 5.0,
        'bank_account_id' => $this->sellerBankAccount->id,
    ]);
});

it('prevents creating multiple ads that cumulatively exceed balance', function () {
    // Seller has 10 BTC
    P2PAdvertisement::factory()->create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::SELL->value,
        'total_amount' => 7.0,
        'available_amount' => 7.0,
        'is_active' => true,
    ]);

    // Try to create another ad for 5.0 (Total 12.0 > 10.0)
    $response = actingAs($this->seller)->postJson('/api/p2p/create-ads', [
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::SELL->value,
        'price' => 50000,
        'total_amount' => 5.0,
        'min_limit' => 1000,
        'max_limit' => 100000,
        'bank_account_id' => $this->sellerBankAccount->id,
    ]);

    $response->assertStatus(400)->assertJsonPath('message', 'Insufficient crypto balance. Your active advertisements already commit a portion of your balance.');
});

it('can dispute a trade', function () {
    $ad = P2PAdvertisement::factory()->create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
    ]);

    $trade = P2PTrade::factory()->create([
        'advertisement_id' => $ad->id,
        'seller_id' => $this->seller->id,
        'buyer_id' => $this->buyer->id,
        'currency_id' => $this->currency->id,
        'status' => TradeStatus::PAID,
    ]);

    $response = actingAs($this->buyer)->postJson("/api/p2p/trades/{$trade->id}/dispute", [
        'reason' => 'I have paid but the seller has not released the crypto.',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('p2p_trades', [
        'id' => $trade->id,
        'status' => TradeStatus::DISPUTED->value,
        'disputed_by' => $this->buyer->id,
        'dispute_reason' => 'I have paid but the seller has not released the crypto.',
    ]);
});

it('prevents creating a sell ad without sufficient crypto balance', function () {
    $response = actingAs($this->seller)->postJson('/api/p2p/create-ads', [
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::SELL->value,
        'price' => 50000,
        'total_amount' => 50.0, // Seller only has 10
        'min_limit' => 5000,
        'max_limit' => 2500000,
        'bank_account_id' => $this->sellerBankAccount->id,
    ]);

    $response->assertStatus(400)->assertJsonPath('message', 'Insufficient crypto balance. Your active advertisements already commit a portion of your balance.');
});

it('can initiate a trade moving crypto to escrow', function () {
    $ad = P2PAdvertisement::factory()->create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::SELL->value,
        'price' => 1000,
        'total_amount' => 5.0,
        'available_amount' => 5.0,
        'min_limit' => 500,
        'max_limit' => 5000,
    ]);

    // Buyer wants to buy 1000 NGN worth of crypto (1 crypto)
    $response = actingAs($this->buyer)->postJson('/api/p2p/initiate-trade', [
        'advertisement_id' => $ad->id,
        'amount' => 1000,
    ]);

    $response->assertStatus(201);

    // Verify crypto deducted from seller's wallet
    $this->sellerWallet->refresh();
    expect((float) $this->sellerWallet->balance)->toEqual(9.0);

    // Verify Escrow wallet received it
    $escrow = SystemWallet::where('type', SystemWalletType::ESCROW)->first();
    expect((float) $escrow->balance)->toEqual(1.0);

    // Verify Trade created
    $trade = P2PTrade::first();
    expect((float) $trade->crypto_amount)->toEqual(1.0);
    expect($trade->status)->toBe(TradeStatus::PENDING);
});

it('can complete a trade and release crypto to buyer', function () {
    $ad = P2PAdvertisement::factory()->create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::SELL->value,
        'price' => 1000,
    ]);

    $trade = P2PTrade::factory()->create([
        'advertisement_id' => $ad->id,
        'seller_id' => $this->seller->id,
        'buyer_id' => $this->buyer->id,
        'currency_id' => $this->currency->id,
        'crypto_amount' => 1.0,
        'fiat_amount' => 1000,
        'status' => TradeStatus::PAID,
    ]);

    $escrow = SystemWallet::where('type', SystemWalletType::ESCROW)->first();
    $escrow->balance = 1.0;
    $escrow->save();

    // Give buyer a wallet so we don't hit CreateWalletAction which requires HdWallet & Gateway mocks
    Wallet::create([
        'user_id' => $this->buyer->id,
        'currency_id' => $this->currency->id,
        'address' => 'buyer-address',
        'balance' => 0.0,
        'status' => 'active',
    ]);

    // Seller confirms payment and completes trade
    $response = actingAs($this->seller)->postJson("/api/p2p/trades/{$trade->id}/complete");

    if ($response->status() !== 200) {
        dump($response->json());
    }

    $response->assertStatus(200);

    // Verify buyer received crypto
    $buyerWallet = $this->buyer->wallet($this->currency->id);
    expect($buyerWallet)->not->toBeNull();
    expect((float) $buyerWallet->balance)->toEqual(1.0);

    // Verify escrow is empty
    $escrow->refresh();
    expect((float) $escrow->balance)->toEqual(0.0);

    $trade->refresh();
    expect($trade->status)->toBe(TradeStatus::COMPLETED);
});

it('can close an advertisement', function () {
    $ad = P2PAdvertisement::factory()->create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
        'is_active' => true,
    ]);

    $response = actingAs($this->seller)->postJson("/api/p2p/close-ads/{$ad->id}");

    $response->assertStatus(200);
    $this->assertDatabaseHas('p2p_advertisements', [
        'id' => $ad->id,
        'is_active' => false,
    ]);
});

it('prevents closing an advertisement with active trades', function () {
    $ad = P2PAdvertisement::factory()->create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
        'is_active' => true,
    ]);

    P2PTrade::factory()->create([
        'advertisement_id' => $ad->id,
        'seller_id' => $this->seller->id,
        'buyer_id' => $this->buyer->id,
        'currency_id' => $this->currency->id,
        'status' => TradeStatus::PENDING,
    ]);

    $response = actingAs($this->seller)->postJson("/api/p2p/close-ads/{$ad->id}");

    $response->assertStatus(400);
    $response->assertJsonPath('message', 'Cannot close advertisement with 1 active trade(s).');
});

it('cancels expired trades and refunds the seller', function () {
    $ad = P2PAdvertisement::factory()->create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::SELL->value,
        'price' => 1000,
        'available_amount' => 5.0,
    ]);

    // Create a trade that is 35 minutes old
    $trade = P2PTrade::factory()->create([
        'advertisement_id' => $ad->id,
        'seller_id' => $this->seller->id,
        'buyer_id' => $this->buyer->id,
        'currency_id' => $this->currency->id,
        'crypto_amount' => 1.0,
        'status' => TradeStatus::PENDING,
        'created_at' => now()->subMinutes(35),
    ]);

    // Manually lock funds in escrow for the test setup
    $escrow = SystemWallet::where('type', SystemWalletType::ESCROW)->first();
    $escrow->balance = 1.0;
    $escrow->save();

    $this->sellerWallet->balance = 9.0;
    $this->sellerWallet->save();

    // Run the command
    $this->artisan('p2p:cancel-expired-trades')
        ->expectsOutput('Found 1 expired trade(s). Proceeding with cancellation...')
        ->expectsOutput("Trade #{$trade->id} cancelled successfully.")
        ->assertExitCode(0);

    // Verify trade status
    $trade->refresh();
    expect($trade->status)->toBe(TradeStatus::CANCELLED);

    // Verify funds returned to seller
    $this->sellerWallet->refresh();
    expect((float) $this->sellerWallet->balance)->toEqual(10.0);

    // Verify available amount returned to ad
    $ad->refresh();
    expect((float) $ad->available_amount)->toEqual(6.0);

    // Verify escrow is empty
    $escrow->refresh();
    expect((float) $escrow->balance)->toEqual(0.0);
});

it('triggers on-chain transfer when completing a gas pump p2p trade', function () {
    $this->currency->update(['is_gaspump' => true, 'symbol' => 'GP']);

    HdWallet::create([
        'currency_id' => $this->currency->id,
        'xpub' => 'xpub123',
        'private_key' => 'priv123',
        'signature_id' => 'sig-123',
        'index' => 1,
    ]);

    $ad = P2PAdvertisement::factory()->create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::SELL->value,
    ]);

    $trade = P2PTrade::factory()->create([
        'advertisement_id' => $ad->id,
        'seller_id' => $this->seller->id,
        'buyer_id' => $this->buyer->id,
        'currency_id' => $this->currency->id,
        'crypto_amount' => 1.0,
        'status' => TradeStatus::PAID,
    ]);

    // System Gas Wallet
    SystemWallet::create([
        'type' => SystemWalletType::GAS,
        'currency_id' => $this->currency->id,
        'address' => 'sys-gas',
        'balance' => 10.0,
    ]);

    // Mock SettingService to return 1% fee
    $settingMock = \Mockery::mock(\App\Domains\Core\Services\SettingService::class);
    $settingMock->shouldReceive('get')->with('p2p_fee_percentage', 0)->andReturn(1.0);
    $this->app->instance(\App\Domains\Core\Services\SettingService::class, $settingMock);

    $feeWallet = SystemWallet::create([
        'type' => SystemWalletType::FEE,
        'currency_id' => $this->currency->id,
        'address' => 'sys-fee',
        'balance' => 0.0,
    ]);

    $gaspumpMock = \Mockery::mock(\App\Domains\Wallet\Contracts\GaspumpServiceInterface::class);
    $gaspumpMock->shouldReceive('gaspumpBatchTransfer')
        ->once()
        ->with(
            \Mockery::any(),
            ['buyer-address', 'sys-fee'],
            ['0.99', '0.01'],
            \Mockery::any(),
            \Mockery::any(),
            \Mockery::any()
        )
        ->andReturn('sig_123');
    $this->app->instance(\App\Domains\Wallet\Contracts\GaspumpServiceInterface::class, $gaspumpMock);

    $cryptoMock = \Mockery::mock(\App\Domains\Wallet\Contracts\CryptoGatewayInterface::class);
    $cryptoMock->shouldReceive('generateAddress')->andReturn('buyer-address');
    $cryptoMock->shouldReceive('subscribeToIncoming')->andReturn(true);
    $this->app->instance(\App\Domains\Wallet\Contracts\CryptoGatewayInterface::class, $cryptoMock);

    $response = actingAs($this->seller)->postJson("/api/p2p/trades/{$trade->id}/complete");

    $response->assertStatus(200);
    $trade->refresh();
    expect($trade->status)->toBe(TradeStatus::COMPLETED);

    // Verify buyer's ledger balance remains unchanged (webhook will handle it)
    $buyerWallet = $this->buyer->wallet($this->currency->id);
    expect((float) $buyerWallet->balance)->toEqual(0.0);

    $feeWallet->refresh();
    expect((float) $feeWallet->balance)->toEqual(0.01);
});

it('activates custodial wallet before creating an ad for gas pump currency', function () {
    $this->currency->update(['is_gaspump' => true]);

    HdWallet::create([
        'currency_id' => $this->currency->id,
        'xpub' => 'xpub123',
        'private_key' => 'priv123',
        'signature_id' => 'sig-123',
        'index' => 1,
    ]);

    // System Gas Wallet
    SystemWallet::create([
        'type' => SystemWalletType::GAS,
        'currency_id' => $this->currency->id,
        'address' => 'sys-gas',
        'balance' => 10.0,
    ]);

    $this->sellerWallet->update(['status' => 'pending']);

    $gaspumpMock = \Mockery::mock(\App\Domains\Wallet\Contracts\GaspumpServiceInterface::class);
    $gaspumpMock->shouldReceive('activateAddress')->once();
    $this->app->instance(\App\Domains\Wallet\Contracts\GaspumpServiceInterface::class, $gaspumpMock);

    $cryptoMock = \Mockery::mock(\App\Domains\Wallet\Contracts\CryptoGatewayInterface::class);
    $this->app->instance(\App\Domains\Wallet\Contracts\CryptoGatewayInterface::class, $cryptoMock);

    $response = actingAs($this->seller)->postJson('/api/p2p/create-ads', [
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::SELL->value,
        'price' => 50000,
        'total_amount' => 1.0,
        'min_limit' => 500,
        'max_limit' => 5000,
        'bank_account_id' => $this->sellerBankAccount->id,
    ]);

    // Should return 400 because it re-throws activation exception after calling activate
    $response->assertStatus(400);
    $response->assertJsonPath('message', 'Wallet not activated, please retry in 5 minutes');

    $this->sellerWallet->refresh();
    expect($this->sellerWallet->status)->toBe(\App\Enum\WalletStatus::ACTIVE);
});

it('deducts percentage fee when completing a p2p trade', function () {
    $this->currency->update(['is_gaspump' => false]);

    $percentage = 1.0; // 1%
    $cryptoAmount = 100.0;
    $feeAmount = 1.0;
    $netAmount = 99.0;

    $settingMock = \Mockery::mock(\App\Domains\Core\Services\SettingService::class);
    $settingMock->shouldReceive('get')->with('p2p_fee_percentage', 0)->andReturn($percentage);
    $this->app->instance(\App\Domains\Core\Services\SettingService::class, $settingMock);

    $feeWallet = SystemWallet::create([
        'type' => SystemWalletType::FEE,
        'currency_id' => $this->currency->id,
        'address' => 'sys-fee',
        'balance' => 0.0,
    ]);

    $ad = P2PAdvertisement::factory()->create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::SELL->value,
    ]);

    $trade = P2PTrade::factory()->create([
        'advertisement_id' => $ad->id,
        'seller_id' => $this->seller->id,
        'buyer_id' => $this->buyer->id,
        'currency_id' => $this->currency->id,
        'crypto_amount' => $cryptoAmount,
        'status' => TradeStatus::PAID,
    ]);

    // Pre-create buyer wallet
    Wallet::create([
        'user_id' => $this->buyer->id,
        'currency_id' => $this->currency->id,
        'address' => 'buyer-address',
        'balance' => 0.0,
        'status' => 'active',
    ]);

    $response = actingAs($this->seller)->postJson("/api/p2p/trades/{$trade->id}/complete");

    if ($response->status() !== 200) {
        dump($response->json());
    }

    $response->assertStatus(200);

    $buyerWallet = $this->buyer->wallet($this->currency->id);
    expect((float) $buyerWallet->balance)->toEqual($netAmount);

    $feeWallet->refresh();
    expect((float) $feeWallet->balance)->toEqual($feeAmount);
});

it('allows buyer to mark a trade as paid by uploading a proof of payment', function () {
    Storage::fake('public');

    $ad = P2PAdvertisement::factory()->create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
    ]);

    $trade = P2PTrade::factory()->create([
        'advertisement_id' => $ad->id,
        'seller_id' => $this->seller->id,
        'buyer_id' => $this->buyer->id,
        'currency_id' => $this->currency->id,
        'status' => TradeStatus::PENDING,
    ]);

    $proof = UploadedFile::fake()->image('receipt.jpg');

    $response = actingAs($this->buyer)->postJson("/api/p2p/trades/{$trade->id}/pay", [
        'payment_proof' => $proof,
    ]);

    $response->assertStatus(200);
    $trade->refresh();
    expect($trade->status)->toBe(TradeStatus::PAID);
    expect($trade->payment_proof)->not->toBeNull();

    $path = str_replace(Storage::disk('public')->url(''), '', $trade->payment_proof);
    Storage::disk('public')->assertExists(ltrim($path, '/'));
});

it('prevents marking a trade as paid without uploading a proof of payment', function () {
    $ad = P2PAdvertisement::factory()->create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
    ]);

    $trade = P2PTrade::factory()->create([
        'advertisement_id' => $ad->id,
        'seller_id' => $this->seller->id,
        'buyer_id' => $this->buyer->id,
        'currency_id' => $this->currency->id,
        'status' => TradeStatus::PENDING,
    ]);

    $response = actingAs($this->buyer)->postJson("/api/p2p/trades/{$trade->id}/pay", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['payment_proof']);

    $trade->refresh();
    expect($trade->status)->toBe(TradeStatus::PENDING);
});

it('fails to create a sell advertisement without a bank account', function () {
    $response = actingAs($this->seller)->postJson('/api/p2p/create-ads', [
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::SELL->value,
        'price' => 50000,
        'total_amount' => 5.0,
        'min_limit' => 5000,
        'max_limit' => 250000,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['bank_account_id']);
});

it('fails to create a sell advertisement with a bank account not owned by the seller', function () {
    $anotherUser = User::factory()->create();
    $anotherBankAccount = \App\Domains\Bank\Models\BankAccount::create([
        'user_id' => $anotherUser->id,
        'bank_id' => $this->bank->id,
        'account_name' => 'Other Account',
        'account_number' => '5555555555',
        'is_active' => true,
    ]);

    $response = actingAs($this->seller)->postJson('/api/p2p/create-ads', [
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::SELL->value,
        'price' => 50000,
        'total_amount' => 5.0,
        'min_limit' => 5000,
        'max_limit' => 250000,
        'bank_account_id' => $anotherBankAccount->id,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['bank_account_id']);
});

it('requires bank account when initiating a trade on a buy advertisement', function () {
    // Ad creator is BUYING crypto (paying fiat to seller)
    $ad = P2PAdvertisement::factory()->create([
        'user_id' => $this->buyer->id, // buyer is ad creator
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::BUY->value,
        'price' => 1000,
        'total_amount' => 5.0,
        'available_amount' => 5.0,
        'min_limit' => 500,
        'max_limit' => 5000,
    ]);

    // Seller (initiator) has 10 BTC
    $this->sellerWallet->balance = 10.0;
    $this->sellerWallet->save();

    // Try initiating trade without bank_account_id (should fail since ad type is BUY)
    $response = actingAs($this->seller)->postJson('/api/p2p/initiate-trade', [
        'advertisement_id' => $ad->id,
        'amount' => 1000,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['bank_account_id']);

    // Initiate trade with seller's bank account (should pass)
    $response = actingAs($this->seller)->postJson('/api/p2p/initiate-trade', [
        'advertisement_id' => $ad->id,
        'amount' => 1000,
        'bank_account_id' => $this->sellerBankAccount->id,
    ]);

    $response->assertStatus(201);

    // Verify trade bank_account_id is set to seller's bank account
    $trade = P2PTrade::latest()->first();
    expect($trade->bank_account_id)->toEqual($this->sellerBankAccount->id);
});

it('can create a buy advertisement and complete the full trade flow', function () {
    // Pre-create buyer wallet so the ad creation does not fail on wallet presence check
    \App\Domains\Wallet\Models\Wallet::create([
        'user_id' => $this->buyer->id,
        'currency_id' => $this->currency->id,
        'address' => 'buyer-address',
        'index' => 1,
    ]);

    // 1. Create BUY advertisement (ad creator is BUYER, i.e., $this->buyer)
    $response = actingAs($this->buyer)->postJson('/api/p2p/create-ads', [
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::BUY->value,
        'price' => 1000,
        'total_amount' => 5.0,
        'min_limit' => 500,
        'max_limit' => 5000,
    ]);

    $response->assertStatus(201);
    $ad = P2PAdvertisement::latest()->first();
    expect($ad->type)->toEqual(AdvertisementType::BUY);

    // 2. Initiate trade on the BUY advertisement as the seller ($this->seller)
    // Seller has 10 BTC
    $this->sellerWallet->balance = 10.0;
    $this->sellerWallet->save();

    $response = actingAs($this->seller)->postJson('/api/p2p/initiate-trade', [
        'advertisement_id' => $ad->id,
        'amount' => 1000, // 1 BTC
        'bank_account_id' => $this->sellerBankAccount->id,
    ]);

    $response->assertStatus(201);
    $trade = P2PTrade::latest()->first();

    // Verify crypto locked in escrow: seller wallet debited by 1.0
    $this->sellerWallet->refresh();
    expect((float) $this->sellerWallet->balance)->toEqual(9.0);

    // 3. Mark trade as paid as the buyer ($this->buyer)
    $file = \Illuminate\Http\UploadedFile::fake()->image('proof.jpg');
    $response = actingAs($this->buyer)->postJson("/api/p2p/trades/{$trade->id}/pay", [
        'payment_proof' => $file,
    ]);

    $response->assertStatus(200);
    $trade->refresh();
    expect($trade->status)->toEqual(TradeStatus::PAID);

    // 4. Complete the trade and release crypto as the seller ($this->seller)
    $response = actingAs($this->seller)->postJson("/api/p2p/trades/{$trade->id}/complete");

    $response->assertStatus(200);
    $trade->refresh();
    expect($trade->status)->toEqual(TradeStatus::COMPLETED);

    // Verify buyer received the crypto (minus p2p_fee_percentage if any, which defaults to 0)
    $buyerWallet = $this->buyer->wallet($this->currency->id);
    expect((float) $buyerWallet->balance)->toEqual(1.0);
});

it('can fetch the authenticated user profile stats when no user ID is specified', function () {
    $response = actingAs($this->seller)->getJson('/api/p2p/profile');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'user' => ['id', 'name', 'initials', 'kyc_status', 'joined_at'],
            'bank_accounts' => [
                '*' => ['id', 'user_id', 'bank_id', 'account_name', 'account_number', 'is_active', 'bank'],
            ],
            'statistics' => ['total_trades', 'total_completed', 'completion_rate', 'avg_transaction_time_seconds', 'avg_transaction_time_formatted'],
        ])
        ->assertJsonPath('user.id', $this->seller->id)
        ->assertJsonPath('bank_accounts.0.id', $this->sellerBankAccount->id)
        ->assertJsonPath('bank_accounts.0.bank.name', $this->bank->name)
        ->assertJsonPath('statistics.total_trades', 0)
        ->assertJsonPath('statistics.total_completed', 0)
        ->assertJsonPath('statistics.completion_rate', 0);
});

it('can fetch another user profile stats by ID', function () {
    $response = actingAs($this->buyer)->getJson("/api/p2p/profile/{$this->seller->id}");

    $response->assertStatus(200)
        ->assertJsonPath('user.id', $this->seller->id);
});

it('correctly calculates total trades, completed trades, completion rate, and avg transaction time', function () {
    $ad = P2PAdvertisement::factory()->create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
    ]);

    // Create 3 trades: 2 completed, 1 cancelled

    // Trade 1: completed, took 10 minutes (600s)
    P2PTrade::factory()->create([
        'advertisement_id' => $ad->id,
        'seller_id' => $this->seller->id,
        'buyer_id' => $this->buyer->id,
        'currency_id' => $this->currency->id,
        'status' => TradeStatus::COMPLETED,
        'created_at' => now()->subMinutes(20),
        'updated_at' => now()->subMinutes(10),
    ]);

    // Trade 2: completed, took 20 minutes (1200s)
    P2PTrade::factory()->create([
        'advertisement_id' => $ad->id,
        'seller_id' => $this->seller->id,
        'buyer_id' => $this->buyer->id,
        'currency_id' => $this->currency->id,
        'status' => TradeStatus::COMPLETED,
        'created_at' => now()->subMinutes(30),
        'updated_at' => now()->subMinutes(10),
    ]);

    // Trade 3: cancelled
    P2PTrade::factory()->create([
        'advertisement_id' => $ad->id,
        'seller_id' => $this->seller->id,
        'buyer_id' => $this->buyer->id,
        'currency_id' => $this->currency->id,
        'status' => TradeStatus::CANCELLED,
    ]);

    // Fetch seller's profile
    $response = actingAs($this->buyer)->getJson("/api/p2p/profile/{$this->seller->id}");

    $response->assertStatus(200)
        ->assertJsonPath('statistics.total_trades', 3)
        ->assertJsonPath('statistics.total_completed', 2)
        // completion rate: 2 / 3 * 100 = 66.67
        ->assertJsonPath('statistics.completion_rate', 66.67)
        // average time: (600 + 1200) / 2 = 900 seconds (15m)
        ->assertJsonPath('statistics.avg_transaction_time_seconds', 900)
        ->assertJsonPath('statistics.avg_transaction_time_formatted', '15m');
});

it('can list all active advertisements and filter them by type', function () {
    // 1. Create a BUY active advertisement
    P2PAdvertisement::factory()->create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::BUY,
        'is_active' => true,
    ]);

    // 2. Create a SELL active advertisement
    P2PAdvertisement::factory()->create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::SELL,
        'is_active' => true,
    ]);

    // 3. Create an INACTIVE advertisement (should not show up at all in indexAds)
    P2PAdvertisement::factory()->create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::SELL,
        'is_active' => false,
    ]);

    // Test: No filter (returns both active ads)
    $response = actingAs($this->buyer)->getJson('/api/p2p/ads');
    $response->assertStatus(200);
    expect(count($response->json('ads')))->toEqual(2);

    // Test: Filter by BUY (returns only 1)
    $response = actingAs($this->buyer)->getJson('/api/p2p/ads?type=buy');
    $response->assertStatus(200);
    expect(count($response->json('ads')))->toEqual(1);
    expect($response->json('ads.0.type'))->toEqual(AdvertisementType::BUY->value);

    // Test: Filter by SELL (returns only 1)
    $response = actingAs($this->buyer)->getJson('/api/p2p/ads?type=sell');
    $response->assertStatus(200);
    expect(count($response->json('ads')))->toEqual(1);
    expect($response->json('ads.0.type'))->toEqual(AdvertisementType::SELL->value);
});

it('can list my advertisements and filter them by type', function () {
    // Seller creates 1 BUY active ad and 1 SELL active ad
    P2PAdvertisement::factory()->create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::BUY,
        'is_active' => true,
    ]);

    P2PAdvertisement::factory()->create([
        'user_id' => $this->seller->id,
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::SELL,
        'is_active' => true,
    ]);

    // Buyer creates 1 active ad (should not show up in Seller's myAds)
    P2PAdvertisement::factory()->create([
        'user_id' => $this->buyer->id,
        'currency_id' => $this->currency->id,
        'type' => AdvertisementType::SELL,
        'is_active' => true,
    ]);

    // Test: No filter for Seller (returns 2 ads)
    $response = actingAs($this->seller)->getJson('/api/p2p/my-ads');
    $response->assertStatus(200);
    expect(count($response->json('ads')))->toEqual(2);

    // Test: Filter by BUY (returns 1 ad)
    $response = actingAs($this->seller)->getJson('/api/p2p/my-ads?type=buy');
    $response->assertStatus(200);
    expect(count($response->json('ads')))->toEqual(1);
    expect($response->json('ads.0.type'))->toEqual(AdvertisementType::BUY->value);

    // Test: Filter by SELL (returns 1 ad)
    $response = actingAs($this->seller)->getJson('/api/p2p/my-ads?type=sell');
    $response->assertStatus(200);
    expect(count($response->json('ads')))->toEqual(1);
    expect($response->json('ads.0.type'))->toEqual(AdvertisementType::SELL->value);
});
