<?php

namespace App\Http\Controllers\Api;

use App\Domains\User\Actions\GenerateOtpAction;
use App\Domains\User\Actions\RegisterUserAction;
use App\Domains\User\Actions\SetTransactionPinAction;
use App\Domains\User\Actions\UpdateTransactionPinAction;
use App\Domains\User\Actions\VerifyOtpAction;
use App\Domains\User\Notifications\ForgotPasswordOtpNotification;
use App\Domains\Wallet\Actions\CreateDefaultWalletsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Http\Requests\Api\SetTransactionPinRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Requests\Api\UpdateTransactionPinRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(
        RegisterRequest $request,
        RegisterUserAction $registerUserAction,
        CreateDefaultWalletsAction $createDefaultWalletsAction
    ) {
        try {
            $user = DB::transaction(function () use ($request, $registerUserAction, $createDefaultWalletsAction) {
                $user = $registerUserAction->execute($request->validated());

                // Cross-domain orchestration: setting up wallets after user registration
                $createDefaultWalletsAction->execute($user);

                return $user;
            });

            Auth::login($user);
            $token = $user->createToken('mobile');
        } catch (\Exception $e) {
            report($e);

            $statusCode = $e->getCode();
            // Validate that the code is a valid HTTP status code, fallback to 500 if not.
            if (! is_numeric($statusCode) || $statusCode < 400 || $statusCode > 599) {
                $statusCode = 500;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token->plainTextToken,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $user = Auth::attempt(['email' => $request->email, 'password' => $request->password]);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect credentials',
            ], 401);
        }

        $token = $request->user()->createToken('mobile');

        return response()->json([
            'success' => true,
            'user' => new UserResource($request->user()),
            'token' => $token->plainTextToken,
        ]);
    }

    public function setTransactionPin(
        SetTransactionPinRequest $request,
        SetTransactionPinAction $action
    ) {
        $user = $request->user();

        $action->execute($user, $request->pin);

        return response()->json([
            'success' => true,
            'user' => new UserResource($request->user()),
            'message' => 'Transaction PIN set successfully.',
        ]);
    }

    public function verifyTransactionPin(Request $request)
    {
        $request->validate([
            'pin' => ['required', 'string', 'size:4', new \App\Rules\MatchTransactionPin],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction PIN matches.',
        ]);
    }

    public function updateTransactionPin(
        UpdateTransactionPinRequest $request,
        UpdateTransactionPinAction $action
    ) {
        $user = $request->user();

        $action->execute($user, $request->current_password, $request->pin);

        return response()->json([
            'success' => true,
            'message' => 'Transaction PIN updated successfully.',
        ]);
    }

    public function verifyOtp(Request $request, VerifyOtpAction $action)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $action->execute($request->user(), $request->otp);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
            'user' => new UserResource($request->user()),
        ]);
    }

    public function resendOtp(Request $request, GenerateOtpAction $action)
    {
        $action->execute($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent to your email.',
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $otp = (string) rand(100000, 999999);
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(10),
            ]);

            $user->notify(new ForgotPasswordOtpNotification($otp));
        }

        return response()->json([
            'success' => true,
            'message' => 'Password reset OTP has been sent to your email.',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        if ($user->otp !== $request->otp) {
            throw ValidationException::withMessages([
                'otp' => ['The provided OTP is incorrect.'],
            ]);
        }

        if ($user->otp_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'otp' => ['The OTP has expired.'],
            ]);
        }

        $user->update([
            'password' => $request->password,
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully.',
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();

        if ($request->has('phone') && $request->phone !== $user->phone) {
            $user->phone = $request->phone;
            $user->phone_verified_at = null;
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');

            // Delete old avatar file if it was uploaded locally
            if ($user->avatar) {
                $storageUrl = Storage::disk('public')->url('');
                if (str_starts_with($user->avatar, $storageUrl)) {
                    $oldPath = str_replace($storageUrl, '', $user->avatar);
                    Storage::disk('public')->delete(ltrim($oldPath, '/'));
                }
            }

            $user->avatar = Storage::disk('public')->url($path);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user' => new UserResource($user),
        ]);
    }
}
