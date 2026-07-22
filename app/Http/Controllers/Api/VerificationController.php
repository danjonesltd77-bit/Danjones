<?php

namespace App\Http\Controllers\Api;

use App\Domains\Kyc\Actions\VerifyNinAction;
use App\Domains\Kyc\Models\UserVerification;
use App\Domains\Kyc\Models\Verification;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\VerifyNinRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerificationController extends Controller
{
    /**
     * List all available verification types along with user verifications.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $userVerifications = $user
            ? UserVerification::where('user_id', $user->id)->with('verification')->latest()->get()
            : [];

        $verifications = Verification::all()->map(function ($verification) use ($user) {
            $latest = $user
                ? UserVerification::where('user_id', $user->id)
                    ->where('verification_id', $verification->id)
                    ->latest()
                    ->first()
                : null;

            $data = $verification->toArray();
            $data['user_verification'] = $latest;

            return $data;
        });

        return ApiResponse::success([
            'data' => $verifications,
            'user_verifications' => $userVerifications,
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
