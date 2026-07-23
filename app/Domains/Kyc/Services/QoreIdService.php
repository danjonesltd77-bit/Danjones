<?php

namespace App\Domains\Kyc\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class QoreIdService
{
    protected string $baseUrl;

    protected string $clientId;

    protected string $secret;

    public function __construct()
    {
        $this->baseUrl = config('services.qoreid.base_url', 'https://api.qoreid.com');
        $this->clientId = (string) config('services.qoreid.client_id');
        $this->secret = (string) config('services.qoreid.secret');
    }

    /**
     * Get access token from QoreID, caching it for subsequent requests.
     */
    public function getAccessToken(): ?string
    {
        return Cache::remember('qoreid_access_token', 3500, function () {
            $response = Http::post($this->baseUrl.'/token', [
                'clientId' => $this->clientId,
                'secret' => $this->secret,
            ]);

            if ($response->successful()) {
                return $response->json('accessToken');
            }

            return null;
        });
    }

    /**
     * Verify NIN using QoreID.
     */
    public function verifyNin(string $nin, array $userData): array
    {
        $token = $this->getAccessToken();

        if (! $token) {
            return [
                'success' => false,
                'message' => 'Unable to authenticate with QoreID',
            ];
        }

        $response = Http::withToken($token)
            ->post($this->baseUrl.'/v1/ng/identities/nin/'.$nin, [
                'firstname' => $userData['firstname'],
                'lastname' => $userData['lastname'],
                'middlename' => $userData['middlename'] ?? null,
            ]);

        if ($response->successful() && $response->json('status.status') == 'verified') {
            return [
                'success' => true,
                'data' => $response->json(),
            ];
        }

        return [
            'success' => false,
            'message' => $response->json('message') ?? 'Verification failed',
            'data' => $response->json(),
            'status' => $response->status(),
        ];
    }
}
