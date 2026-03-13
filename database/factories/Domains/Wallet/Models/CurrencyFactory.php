<?php

namespace Database\Factories\Domains\Wallet\Models;

use App\Domains\Wallet\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Wallet\Models\Currency>
 */
class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'is_crypto' => true,
            'is_gaspump' => false,
            'is_active' => true,
            'decimal' => 8,
            'token_currency' => 'BTC',
            'fee' => 0.0001,
        ];
    }
}
