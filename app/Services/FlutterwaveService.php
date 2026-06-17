<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlutterwaveService
{
    protected string $baseUrl;

    protected string $secretKey;

    public function __construct()
    {
        $this->baseUrl = 'https://api.flutterwave.com/v3';
        $this->secretKey = config('services.flutterwave.secret_key') ?? '';
    }

    /**
     * Resolve account number to account name using Flutterwave.
     *
     * @throws \Exception
     */
    public function resolveAccountNumber(string $accountNumber, string $bankCode): array
    {
        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/accounts/resolve", [
                'account_number' => $accountNumber,
                'account_bank' => $bankCode,
            ]);

        if ($response->failed()) {
            Log::error('Flutterwave Account Resolution Failed', [
                'response' => $response->json(),
                'account_number' => $accountNumber,
                'bank_code' => $bankCode,
            ]);

            $message = $response->json()['message'] ?? 'Could not resolve account name via Flutterwave.';
            throw new \Exception($message);
        }

        return $response->json()['data'];
    }

    /**
     * Initiate a transfer using Flutterwave.
     *
     * @throws \Exception
     */
    public function initiateTransfer(array $payload): array
    {
        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transfers", $payload);

        if ($response->failed()) {
            Log::error('Flutterwave Transfer Failed', [
                'response' => $response->json(),
                'payload' => $payload,
            ]);

            $message = $response->json()['message'] ?? 'Transfer initiation failed via Flutterwave.';
            throw new \Exception($message);
        }

        return $response->json()['data'];
    }

    /**
     * Get list of banks from Flutterwave.
     *
     * @throws \Exception
     */
    public function getBanks(string $country = 'NG'): array
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/banks/{$country}");

        if ($response->failed()) {
            Log::error('Flutterwave Get Banks Failed', [
                'response' => $response->json(),
                'country' => $country,
            ]);

            $message = $response->json()['message'] ?? 'Could not fetch banks from Flutterwave.';
            throw new \Exception($message);
        }

        return $response->json()['data'];
    }
}
