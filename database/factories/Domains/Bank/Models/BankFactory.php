<?php

namespace Database\Factories\Domains\Bank\Models;

use App\Domains\Bank\Models\Bank;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Bank\Models\Bank>
 */
class BankFactory extends Factory
{
    protected $model = Bank::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Bank',
            'code' => fake()->unique()->numerify('###'),
            'is_active' => true,
        ];
    }
}
