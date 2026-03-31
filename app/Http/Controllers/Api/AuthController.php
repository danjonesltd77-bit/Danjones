<?php

namespace App\Http\Controllers\Api;

use App\Domains\User\Actions\RegisterUserAction;
use App\Domains\User\Actions\SetTransactionPinAction;
use App\Domains\User\Actions\UpdateTransactionPinAction;
use App\Domains\Wallet\Actions\CreateDefaultWalletsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\SetTransactionPinRequest;
use App\Http\Requests\Api\UpdateTransactionPinRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            if (!is_numeric($statusCode) || $statusCode < 400 || $statusCode > 599) {
                $statusCode = 500;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token->plainTextToken
        ]);
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = Auth::attempt(['email' => $request->email, 'password' => $request->password]);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect credentials'
            ], 401);
        }

        $token = $request->user()->createToken('mobile');

        return response()->json([
            'success' => true,
            'user'=> new UserResource($request->user()),
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
            'user'=> new UserResource($request->user()),
            'message' => 'Transaction PIN set successfully.',
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
}
