<?php

namespace Database\Factories\Domains\Wallet\Models;

use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\HdWallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Wallet\Models\HdWallet>
 */
class HdWalletFactory extends Factory
{
    protected $model = HdWallet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'currency_id' => Currency::factory(),
            'signature_id' => $this->faker->uuid(),
            'xpub' => $this->faker->sha256(),
            'index' => 20,
        ];
    }
}
