<?php

namespace App\Domains\Kyc\Actions;

use App\Domains\Kyc\Models\UserVerification;
use App\Domains\Kyc\Models\Verification;
use App\Domains\Kyc\Services\QoreIdService;
use App\Models\User;

class VerifyNinAction
{
    public function __construct(protected QoreIdService $qoreIdService) {}

    /**
     * Execute the NIN verification action.
     */
    public function execute(User $user, array $data): array
    {
        $verificationType = Verification::where('name', 'NIN')->firstOrFail();

        // Check if user is already verified
        $isVerified = UserVerification::where('user_id', $user->id)
            ->where('verification_id', $verificationType->id)
            ->where('status', 'approved')
            ->exists();

        if ($isVerified) {
            return [
                'success' => false,
                'message' => 'You are already verified.',
            ];
        }

        // Check if user has reached the limit of failed attempts
        $failedAttempts = UserVerification::where('user_id', $user->id)
            ->where('verification_id', $verificationType->id)
            ->where('status', 'rejected')
            ->count();

        if ($failedAttempts >= 5) {
            return [
                'success' => false,
                'message' => 'You have reached the maximum number of failed verification attempts.',
            ];
        }

        // Check if NIN is already used by another verified user
        $ninUsed = UserVerification::where('verification_id', $verificationType->id)
            ->where('status', 'approved')
            ->where('data->nin', $data['nin'])
            ->exists();

        if ($ninUsed) {
            return [
                'success' => false,
                'message' => 'This NIN has already been verified by another user.',
            ];
        }

        $result = $this->qoreIdService->verifyNin($data['nin'], [
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'middlename' => $data['middlename'] ?? null,
        ]);

        $status = $result['success'] ? 'approved' : 'rejected';

        if ($result['success']) {
            $nameParts = array_filter([
                $data['firstname'],
                $data['middlename'] ?? null,
                $data['lastname'],
            ]);

            $user->update([
                'name' => implode(' ', $nameParts),
            ]);
        }

        // Record the verification attempt
        UserVerification::create([
            'user_id' => $user->id,
            'verification_id' => $verificationType->id,
            'status' => $status,
            'data' => array_merge($result['data'] ?? [], ['nin' => $data['nin']]),
            'reference' => $result['data']['reference'] ?? $result['reference'] ?? null,
            'reason' => $result['message'] ?? null,
        ]);

        $title = $status === 'approved' ? 'KYC Verification Successful' : 'KYC Verification Failed';
        $msg = $status === 'approved'
            ? 'Your NIN verification was successful.'
            : 'Your NIN verification was rejected. '.($result['message'] ?? '');

        send_notification($user, $title, $msg, 'kyc_verification', ['status' => $status, 'type' => 'NIN']);

        return $result;
    }
}
