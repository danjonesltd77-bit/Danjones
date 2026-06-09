<?php

namespace Tests\Feature\Domains\Wallet\Api;

use App\Domains\Wallet\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VerifyFlutterwaveDepositTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Currency $ngn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->ngn = Currency::factory()->create([
            'symbol' => 'NGN',
            'name' => 'Naira',
            'is_crypto' => false,
            'decimal' => 2,
        ]);

        \App\Domains\Wallet\Models\Wallet::create([
            'user_id' => $this->user->id,
            'currency_id' => $this->ngn->id,
            'balance' => 0,
            'address' => 'NGN-ADDR-'.$this->user->id,
        ]);
    }

    public function test_it_can_verify_and_confirm_flutterwave_deposit()
    {
        $reference = 'flw-tx-123';

        Http::fake([
            'https://api.flutterwave.com/v3/transactions/verify_by_reference*' => Http::response([
                'status' => 'success',
                'message' => 'Transaction fetched successfully',
                'data' => [
                    'id' => 12345,
                    'tx_ref' => $reference,
                    'flw_ref' => 'FLW-123',
                    'amount' => 5000,
                    'currency' => 'NGN',
                    'status' => 'successful',
                    'customer' => [
                        'email' => $this->user->email,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/wallets/deposit/flutterwave/verify', [
                'reference' => $reference,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('amount', 5000);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'reference' => $reference,
            'amount' => 5000,
            'action' => 'deposit',
            'status' => 'completed',
        ]);

        $this->assertEquals(5000, $this->user->wallet($this->ngn->id)->balance);
    }

    public function test_it_does_not_double_credit_for_same_reference()
    {
        $reference = 'flw-tx-123';

        // Pre-create transaction
        $wallet = $this->user->wallet($this->ngn->id);
        $wallet->balance = 5000;
        $wallet->save();

        \App\Domains\Wallet\Models\Transaction::create([
            'user_id' => $this->user->id,
            'wallet_id' => $wallet->id,
            'wallet_type' => get_class($wallet),
            'currency_id' => $this->ngn->id,
            'action' => 'deposit',
            'amount' => 5000,
            'usd' => 5000 / 1500,
            'type' => 'credit',
            'previous_balance' => 0,
            'current_balance' => 5000,
            'reference' => $reference,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/wallets/deposit/flutterwave/verify', [
                'reference' => $reference,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Transaction already processed.');

        $this->assertEquals(5000, $this->user->wallet($this->ngn->id)->balance);
    }

    public function test_it_fails_if_transaction_status_is_not_successful()
    {
        $reference = 'flw-tx-failed';

        Http::fake([
            'https://api.flutterwave.com/v3/transactions/verify_by_reference*' => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => 'failed',
                    'currency' => 'NGN',
                    'amount' => 5000,
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/wallets/deposit/flutterwave/verify', [
                'reference' => $reference,
            ]);

        $response->assertStatus(500);
        $this->assertEquals(0, $this->user->wallet($this->ngn->id)->balance);
    }
}
