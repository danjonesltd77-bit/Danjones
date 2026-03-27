<?php

namespace Database\Seeders;

use App\Domains\Kyc\Models\Verification;
use Illuminate\Database\Seeder;

class VerificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $verifications = [
            ['name' => 'NIN'],
        ];

        foreach ($verifications as $verification) {
            Verification::updateOrCreate(
                ['name' => $verification['name']],
                $verification
            );
        }
    }
}
