<?php

namespace Database\Seeders;

use App\Domains\Wallet\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencyCoingeckoIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mapping = [
            'BTC' => 'bitcoin',
            'TRX' => 'tron',
            'USDT' => 'tether',
            'DOGE' => 'dogecoin',
            'BSC' => 'binancecoin',
            'ETH' => 'ethereum',
        ];

        foreach ($mapping as $symbol => $coingeckoId) {
            Currency::where('symbol', $symbol)->update([
                'coingecko_id' => $coingeckoId,
            ]);
        }
    }
}
