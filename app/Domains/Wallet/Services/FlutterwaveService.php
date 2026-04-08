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
     *
     * @param string $transactionId
     * @return array
     * @throws Exception
     */
    public function verifyTransaction(string $transactionId): array
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transactions/{$transactionId}/verify");

        if (!$response->successful()) {
            Log::error('Flutterwave verification request failed', [
                'transaction_id' => $transactionId,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new Exception('Failed to verify transaction with Flutterwave.');
        }

        $data = $response->json();

        if ($data['status'] !== 'success') {
            throw new Exception($data['message'] ?? 'Transaction verification failed.');
        }

        return $data['data'];
    }
}
