<?php

namespace Database\Factories\Domains\Bank\Models;

use App\Domains\Bank\Models\Bank;
use App\Domains\Bank\Models\BankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Bank\Models\BankAccount>
 */
class BankAccountFactory extends Factory
{
    protected $model = BankAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bank_id' => Bank::factory(),
            'account_name' => fake()->name(),
            'account_number' => fake()->numerify('##########'),
            'is_active' => true,
        ];
    }
}
