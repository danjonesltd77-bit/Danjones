<?php

namespace App\Http\Controllers\Api;

use App\Domains\Kyc\Actions\VerifyNinAction;
use App\Domains\Kyc\Models\Verification;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\VerifyNinRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class VerificationController extends Controller
{
    /**
     * List all available verification types.
     */
    public function index(): JsonResponse
    {
        return ApiResponse::success([
            'data' => Verification::all(),
        ]);
    }

    /**
     * Verify National Identity Number (NIN).
     */
    public function verifyNin(VerifyNinRequest $request, VerifyNinAction $action): JsonResponse
    {
        try {
            $result = $action->execute($request->user(), $request->validated());

            if ($result['success']) {
                return ApiResponse::success([
                    'message' => 'NIN verification successful.',
                ]);
            }

            return ApiResponse::error(
                $result['message'] ?? 'NIN verification failed.',
                422
            );
        } catch (\Exception $e) {
            Log::error('NIN Verification Error: '.$e->getMessage());

            return ApiResponse::error(
                'An error occurred during verification. Please try again later.',
                500
            );
        }
    }
}
