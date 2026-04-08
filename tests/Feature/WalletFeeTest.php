<?php

namespace Tests\Feature;

use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class WalletFeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_onchain_fee_estimate()
    {
        $user = User::factory()->create();
        $currency = Currency::factory()->create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal' => 8, 'is_active' => true]);

        $mockGateway = Mockery::mock(CryptoGatewayInterface::class);
        $mockGateway->shouldReceive('estimateOnchainFee')
            ->once()
            ->with(Mockery::on(fn ($c) => $c->id === $currency->id), 0.1)
            ->andReturn(0.0001);

        $this->app->instance(CryptoGatewayInterface::class, $mockGateway);

        $response = $this->actingAs($user)->getJson("/api/wallets/send-fee?currency_id={$currency->id}&amount=0.1");

        $response->assertStatus(200)
            ->assertJsonPath('fee', 0.0001)
            ->assertJsonStructure([
                'fee',
                'fee_usd',
                'rate_usd',
                'is_high_fee',
                'max_fee_usd',
            ]);
    }
}
