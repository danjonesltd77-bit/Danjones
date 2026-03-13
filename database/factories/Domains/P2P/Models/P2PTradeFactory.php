<?php

namespace Database\Factories\Domains\P2P\Models;

use App\Domains\P2P\Models\P2PAdvertisement;
use App\Domains\P2P\Models\P2PTrade;
use App\Domains\Wallet\Models\Currency;
use App\Enum\TradeStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\P2P\Models\P2PTrade>
 */
class P2PTradeFactory extends Factory
{
    protected $model = P2PTrade::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'advertisement_id' => P2PAdvertisement::factory(),
            'seller_id' => User::factory(),
            'buyer_id' => User::factory(),
            'currency_id' => Currency::factory(),
            'crypto_amount' => fake()->randomFloat(4, 0.01, 2),
            'fiat_amount' => fake()->randomFloat(2, 5000, 100000),
            'status' => fake()->randomElement([TradeStatus::PENDING, TradeStatus::PAID, TradeStatus::COMPLETED]),
        ];
    }
}
