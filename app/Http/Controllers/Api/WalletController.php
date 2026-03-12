<?php

namespace App\Http\Controllers\Api;

use App\Domains\Wallet\Actions\CreateWalletAction;
use App\Domains\Wallet\Models\Currency;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateWalletRequest;
use App\Http\Resources\CurrencyResource;
use App\Http\Resources\WalletResource;
use App\Http\Responses\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * Return all active currencies.
     */
    public function currencies(): JsonResponse
    {
        $currencies = Currency::where('is_active', true)->get();

        $resource = CurrencyResource::collection($currencies)->resolve();
        return ApiResponse::success(['currencies' => $resource], 200);
    }

    /**
     * Return the authenticated user's wallets with their currency.
     */
    public function wallets(Request $request): JsonResponse
    {
        $wallets = $request->user()
            ->wallets()
            ->with('currency')
            ->get();

        $resource = WalletResource::collection($wallets)->resolve();
        return ApiResponse::success(['wallets' => $resource], 200);
    }

    function wallet(int $currencyId)
    {
        $wallet = Auth::user()->wallet($currencyId);

        if (!$wallet) { 
            return ApiResponse::error('Wallet not found', 404);
        }

        $resource = (new WalletResource($wallet->load(['currency', 'transactions'])))->resolve();
        return ApiResponse::success(['wallet' => $resource], 200);
    }

    /**
     * Create a new wallet for the authenticated user.
     */
    public function create(CreateWalletRequest $request, CreateWalletAction $action): JsonResponse
    {
        try {
            $wallet = DB::transaction(function () use ($request, $action) {
                return $action->execute(
                    $request->user(),
                    $request->integer('currency_id'),
                );
            });
        } catch (Exception $e) {
            $code = $e->getCode();
            $code = (is_int($code) && $code >= 100 && $code < 600) ? $code : 500;
            return ApiResponse::error($e->getMessage(), $code);
        }

        $resource = (new WalletResource($wallet->load('currency')))->resolve();
        return ApiResponse::success(['wallet' => $resource], 201);
    }
}
