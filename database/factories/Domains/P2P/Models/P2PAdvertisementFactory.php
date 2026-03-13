<?php

namespace Database\Factories\Domains\P2P\Models;

use App\Domains\P2P\Models\P2PAdvertisement;
use App\Domains\Wallet\Models\Currency;
use App\Enum\AdvertisementType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\P2P\Models\P2PAdvertisement>
 */
class P2PAdvertisementFactory extends Factory
{
    protected $model = P2PAdvertisement::class;

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
            'type' => fake()->randomElement([AdvertisementType::BUY, AdvertisementType::SELL]),
            'price' => fake()->randomFloat(2, 50000, 100000000), // Prices in NGN
            'total_amount' => fake()->randomFloat(4, 0.1, 10),
            'available_amount' => fake()->randomFloat(4, 0.1, 10),
            'min_limit' => fake()->randomFloat(2, 1000, 10000), // Min 1k NGN
            'max_limit' => fake()->randomFloat(2, 50000, 2000000), // Max 2m NGN
            'terms' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
