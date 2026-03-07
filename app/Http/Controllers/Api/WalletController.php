<?php

namespace App\Http\Controllers\Api;

use App\Domains\Wallet\Actions\CreateWalletAction;
use App\Domains\Wallet\Models\Currency;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateWalletRequest;
use App\Http\Resources\CurrencyResource;
use App\Http\Resources\WalletResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        return response()->json(['currencies' => $resource], 200);
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
        return response()->json(['wallets' => $resource], 200);
    }

    /**
     * Create a new wallet for the authenticated user.
     */
    public function create(CreateWalletRequest $request, CreateWalletAction $action): JsonResponse
    {
        $wallet = DB::transaction(function () use ($request, $action) {
            return $action->execute(
                $request->user(),
                $request->integer('currency_id'),
            );
        });

        $resource = (new WalletResource($wallet->load('currency')))->resolve();
        return response()->json(['wallet' => $resource], 201);
    }
}
