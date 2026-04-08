<?php

namespace Database\Factories\Domains\Wallet\Models;

use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\SystemWallet;
use App\Enum\SystemWalletType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Wallet\Models\SystemWallet>
 */
class SystemWalletFactory extends Factory
{
    protected $model = SystemWallet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(SystemWalletType::cases()),
            'currency_id' => Currency::factory(),
            'address' => $this->faker->uuid(),
            'balance' => 0.0,
        ];
    }
}
