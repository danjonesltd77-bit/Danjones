<?php

namespace App\Domains\Wallet\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class TatumApiClient
{
    private function client($version = 'v3', $is_gaspump = false): PendingRequest
    {
        $apiKey = config('services.tatum.api_key');
        $baseUrl = config('services.tatum.base_url', 'https://api.tatum.io');

        $baseUrl .= "/$version";

        if ($is_gaspump) {
            $apiKey = config('services.tatum.api_key_gaspump');
        }

        return Http::withHeaders([
            'x-api-key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->baseUrl($baseUrl);
    }

    public function get(string $endpoint, $version = 'v3', $is_gaspump = false)
    {
        return $this->client($version, $is_gaspump)->get($endpoint);
    }

    public function post(string $endpoint, array $data = [], $version = 'v3', $is_gaspump = false)
    {
        return $this->client($version, $is_gaspump)->post($endpoint, $data);
    }
}
