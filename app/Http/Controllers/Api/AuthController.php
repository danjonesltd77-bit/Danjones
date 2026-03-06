<?php

namespace App\Http\Controllers\Api;

use App\Domains\User\Actions\RegisterUserAction;
use App\Domains\Wallet\Actions\CreateDefaultWalletsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
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
            DB::transaction(function () use ($request, $registerUserAction, $createDefaultWalletsAction, &$token) {
                $user = $registerUserAction->execute($request->validated());

                // Cross-domain orchestration: setting up wallets after user registration
                $createDefaultWalletsAction->execute($user);

                Auth::login($user);

                $token = $user->createToken('mobile');

                return response()->json([
                    'success' => true,
                    'token' => $token->plainTextToken
                ]);
            });
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
            ]);
        }

        $token = $request->user()->createToken('mobile');

        return response()->json([
            'success' => true,
            'token' => $token->plainTextToken,
        ]);
    }

    public function user()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'user' => $user,
            // 'version' => SettingController::get('version')
        ]);
    }
}
