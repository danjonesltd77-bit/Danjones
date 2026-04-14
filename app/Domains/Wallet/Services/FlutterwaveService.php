<?php

namespace App\Domains\Wallet\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlutterwaveService
{
    protected ?string $secretKey;
    protected string $baseUrl = 'https://api.flutterwave.com/v3';

    public function __construct()
    {
        $this->secretKey = config('services.flutterwave.secret_key');
    }

    /**
     * Verify a transaction using Flutterwave's transaction ID.
     */
    public function verifyTransactionById(string $transactionId): array
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transactions/{$transactionId}/verify");

        return $this->handleResponse($response, $transactionId);
    }

    /**
     * Verify a transaction using the merchant's transaction reference (tx_ref).
     */
    public function verifyTransactionByReference(string $reference): array
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transactions/verify_by_reference", [
                'tx_ref' => $reference,
            ]);

        return $this->handleResponse($response, $reference);
    }

    /**
     * Handle the Flutterwave API response.
     *
     * @throws Exception
     */
    protected function handleResponse($response, string $identifier): array
    {
        if (!$response->successful()) {
            Log::error('Flutterwave verification request failed', [
                'identifier' => $identifier,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new Exception('Failed to verify transaction with Flutterwave.');
        }

        $data = $response->json();

        if (($data['status'] ?? '') !== 'success') {
            throw new Exception($data['message'] ?? 'Transaction verification failed.');
        }

        return $data['data'];
    }
}
