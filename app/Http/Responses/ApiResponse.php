<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Return a graceful success JSON response.
     * We don't wrap data in a structured default wrapper because the application
     * specifically relies on mapping resource keys (e.g., ['wallets' => ...]).
     *
     * @param array $data Assosciative array carrying the main payload.
     * @param int $status HTTP status code.
     * @return JsonResponse
     */
    public static function success(array $data = [], int $status = 200): JsonResponse
    {
        return response()->json($data, $status);
    }

    /**
     * Return a graceful error JSON response.
     *
     * @param string $message The user friendly error message.
     * @param int $status HTTP status code.
     * @param mixed $errors Extended validation or stack trace errors.
     * @return JsonResponse
     */
    public static function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        // Enforce valid HTTP response codes
        if ($status < 100 || $status >= 600) {
            $status = 500;
        }

        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
