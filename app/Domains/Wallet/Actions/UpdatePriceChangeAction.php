<?php

namespace App\Domains\Wallet\Actions;

use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Services\CoinGeckoService;
use Illuminate\Support\Facades\Log;

class UpdatePriceChangeAction
{
    public function __construct(private CoinGeckoService $coinGeckoService) {}

    public function execute(): void
    {
        $currencies = Currency::whereNotNull('coingecko_id')->get();

        if ($currencies->isEmpty()) {
            return;
        }

        $ids = $currencies->pluck('coingecko_id')->implode(',');

        try {
            $data = $this->coinGeckoService->getSimplePrice($ids);

            if (! empty($data)) {
                foreach ($currencies as $currency) {
                    $coingeckoId = $currency->coingecko_id;
                    if (isset($data[$coingeckoId]['usd_24h_change'])) {
                        $currency->update([
                            'price_change_24h' => (float) $data[$coingeckoId]['usd_24h_change'],
                        ]);
                    }
                }

                Log::info('Successfully updated price changes for '.$currencies->count().' currencies from CoinGecko.');
            } else {
                Log::error('Failed to fetch price changes from CoinGecko or received empty data.');
            }
        } catch (\Exception $e) {
            Log::error('Error updating price changes from CoinGecko: '.$e->getMessage());
        }
    }
}
