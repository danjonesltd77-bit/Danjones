<?php

namespace Database\Factories\Domains\Wallet\Models;

use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\Wallet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Wallet\Models\Wallet>
 */
class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'currency_id' => Currency::factory(),
            'address' => $this->faker->uuid(),
            'balance' => 0.0,
            'address_balance' => 0.0,
            'index' => 0,
            'status' => 'active',
        ];
    }
}
