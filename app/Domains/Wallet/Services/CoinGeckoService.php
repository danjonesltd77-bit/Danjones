<?php

namespace App\Domains\Wallet\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class CoinGeckoService
{
    private function client(): PendingRequest
    {
        $baseUrl = config('services.coingecko.base_url', 'https://api.coingecko.com/api/v3');

        return Http::baseUrl($baseUrl);
    }

    public function getSimplePrice(string $ids, string $vsCurrencies = 'usd'): array
    {
        $response = $this->client()->get('/simple/price', [
            'ids' => $ids,
            'vs_currencies' => $vsCurrencies,
            'include_24hr_change' => 'true',
        ]);

        return $response->successful() ? $response->json() : [];
    }
}
