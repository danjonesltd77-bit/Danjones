<?php

namespace Tests\Feature\Admin;

use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\Transaction;
use App\Domains\Wallet\Models\Wallet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserViewTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $customer;

    protected Currency $ngnCurrency;

    protected function setUp(): void
    {
        parent::setUp();

        // Implicitly grant super-admin role all permissions is defined in AppServiceProvider
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Create admin with super-admin role
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'pin' => Hash::make('1234'),
        ]);
        $this->admin->assignRole('super-admin');

        // Create customer
        $this->customer = User::factory()->create([
            'email' => 'customer@test.com',
        ]);

        // Create NGN Currency (which is currency_id = 1 usually)
        $this->ngnCurrency = Currency::factory()->create([
            'id' => 1,
            'name' => 'Naira',
            'symbol' => 'NGN',
            'decimal' => 2,
            'is_crypto' => false,
            'is_active' => true,
        ]);
    }

    public function test_unauthorized_user_cannot_credit_wallet(): void
    {
        // A regular user without 'super-admin' role or 'manage wallets' permission
        $regularUser = User::factory()->create([
            'pin' => Hash::make('1234'),
        ]);

        // Mock market data
        $mockMarketData = \Mockery::mock(MarketDataGatewayInterface::class);
        $mockMarketData->shouldReceive('getUsdNgnRate')->andReturn(1500.0);
        $mockMarketData->shouldReceive('getExchangeRate')->andReturn(1.0);
        $this->app->instance(MarketDataGatewayInterface::class, $mockMarketData);

        $this->actingAs($regularUser);

        Livewire::test(\App\Livewire\Admin\Users\UserView::class, ['user' => $this->customer])
            ->set('creditAmount', '5000')
            ->set('creditDescription', 'Unauthorized credit test')
            ->set('adminPin', '1234')
            ->call('processCredit')
            ->assertForbidden();
    }

    public function test_admin_can_credit_user_naira_wallet_with_valid_details_and_pin(): void
    {
        // Mock market data gateway
        $mockMarketData = \Mockery::mock(MarketDataGatewayInterface::class);
        $mockMarketData->shouldReceive('getUsdNgnRate')->andReturn(1000.0);
        $mockMarketData->shouldReceive('getExchangeRate')->andReturn(1.0);
        $this->app->instance(MarketDataGatewayInterface::class, $mockMarketData);

        // Pre-create user's Naira wallet
        $wallet = $this->customer->wallets()->create([
            'name' => $this->customer->name,
            'currency_id' => $this->ngnCurrency->id,
            'address' => $this->customer->email,
            'status' => 'active',
            'balance' => 1000.00,
        ]);

        $this->actingAs($this->admin);

        Livewire::test(\App\Livewire\Admin\Users\UserView::class, ['user' => $this->customer])
            ->set('creditAmount', '5000.50')
            ->set('creditDescription', 'Refund for failed deposit')
            ->set('adminPin', '1234')
            ->call('processCredit')
            ->assertHasNoErrors()
            ->assertSet('creditAmount', '')
            ->assertSet('creditDescription', '')
            ->assertSet('adminPin', '');

        // Verify balance was updated
        $wallet->refresh();
        $this->assertEquals(6000.50, (float) $wallet->balance);

        // Verify transaction ledger entry was recorded
        $transaction = Transaction::where('wallet_id', $wallet->id)
            ->where('action', 'deposit')
            ->firstOrFail();

        $this->assertEquals(5000.50, (float) $transaction->amount);
        $this->assertEquals(5.0005, (float) $transaction->usd); // 5000.50 / 1000.0 rate
        $this->assertEquals('credit', $transaction->type->value);
        $this->assertEquals(1000.00, (float) $transaction->previous_balance);
        $this->assertEquals(6000.50, (float) $transaction->current_balance);
        $this->assertStringContainsString('Refund for failed deposit', $transaction->description);
    }

    public function test_it_validates_credit_inputs(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(\App\Livewire\Admin\Users\UserView::class, ['user' => $this->customer])
            ->set('creditAmount', '-500')
            ->set('creditDescription', '')
            ->set('adminPin', '12')
            ->call('processCredit')
            ->assertHasErrors([
                'creditAmount' => 'gt',
                'creditDescription' => 'required',
                'adminPin' => 'size',
            ]);
    }

    public function test_it_fails_on_incorrect_pin(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(\App\Livewire\Admin\Users\UserView::class, ['user' => $this->customer])
            ->set('creditAmount', '1000')
            ->set('creditDescription', 'Valid description')
            ->set('adminPin', '9999') // incorrect PIN
            ->call('processCredit')
            ->assertHasErrors(['adminPin']);
    }
}
