<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'usd_ngn_rate',
                'value' => '1500.0',
                'type' => 'float',
                'description' => 'Exchange rate from USD to NGN',
            ],
            [
                'key' => 'sell_fee_percentage',
                'value' => '1.5',
                'type' => 'float',
                'description' => 'Platform fee percentage for selling crypto',
            ],
        ];

        foreach ($settings as $setting) {
            \App\Domains\Core\Models\Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
