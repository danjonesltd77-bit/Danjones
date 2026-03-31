<?php

namespace App\Http\Controllers\Api;

use App\Domains\Wallet\Actions\CreateWalletAction;
use App\Domains\Wallet\Actions\SellAction;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateWalletRequest;
use App\Http\Requests\Api\SellRequest;
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
    public function __construct(
        protected MarketDataGatewayInterface $marketDataGateway
    ) {}

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
        $user = $request->user();

        $wallets = $user->wallets()
            ->with('currency')
            ->get();

        $walletCurrencyIds = $wallets->pluck('currency_id');

        $availableCurrencies = Currency::where('is_active', true)
            ->whereNotIn('id', $walletCurrencyIds)
            ->get();

        $usdNgnRate = $this->marketDataGateway->getUsdNgnRate();

        $wallets->each(function ($wallet) use ($usdNgnRate) {
            $price = $wallet->currency->is_crypto
                ? $this->marketDataGateway->getExchangeRate($wallet->currency_id)
                : 1 / $usdNgnRate;

            $wallet->balance_usd = (float) $wallet->balance * $price;
        });

        return ApiResponse::success([
            'wallets' => WalletResource::collection($wallets)->resolve(),
            'available_currencies' => CurrencyResource::collection($availableCurrencies)->resolve(),
        ], 200);
    }

    /**
     * Return the user's wallet balances and current rates.
     */
    public function rates(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallets = $user->wallets()->whereHas('currency', function ($query) {
            $query->where('is_crypto', true);
        })->with('currency')->get();
        $usdNgnRate = $this->marketDataGateway->getUsdNgnRate();

        $rates = $wallets->map(function ($wallet) use ($usdNgnRate) {
            $rate = $wallet->currency->is_crypto
                ? $this->marketDataGateway->getExchangeRate($wallet->currency_id)
                : 1 / $usdNgnRate;

            return [
                'currency_id' => $wallet->currency_id,
                'symbol'      => $wallet->currency->symbol,
                'name'        => $wallet->currency->name,
                'balance'     => (float) $wallet->balance,
                'rate_usd'    => (float) $rate,
                'balance_usd' => (float) $wallet->balance * $rate,
            ];
        });

        return ApiResponse::success([
            'usd_ngn_rate' => (float) $usdNgnRate,
            'wallets'      => $rates,
        ], 200);
    }

    public function wallet(int $currencyId)
    {
        $wallet = Auth::user()->wallet($currencyId);

        if (! $wallet) {
            return ApiResponse::error('Wallet not found', 404);
        }

        $usdNgnRate = $this->marketDataGateway->getUsdNgnRate();
        $price = $wallet->currency->is_crypto
            ? $this->marketDataGateway->getExchangeRate($wallet->currency_id)
            : 1 / $usdNgnRate;

        $wallet->balance_usd = (float) $wallet->balance * $price;

        $resource = (new WalletResource($wallet->load([
            'currency',
            'transactions' => fn ($query) => $query->where('action', '!=', \App\Enum\TransactionAction::FEE->value),
        ])))->resolve();

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

    /**
     * Sell cryptocurrency for NGN.
     */
    public function sell(SellRequest $request, SellAction $action): JsonResponse
    {
        try {
            $user = $request->user();
            $currencyId = $request->integer('currency_id');
            $amount = $request->float('amount');

            $cryptoWallet = $user->wallets()
                ->where('currency_id', $currencyId)
                ->first();

            if (! $cryptoWallet) {
                return ApiResponse::error('Crypto wallet not found.', 404);
            }

            $result = $action->execute($user, $cryptoWallet, $amount);

            return ApiResponse::success($result, 200);
        } catch (Exception $e) {
            $code = $e->getCode();
            $code = (is_int($code) && $code >= 100 && $code < 600) ? $code : 500;

            return ApiResponse::error($e->getMessage(), $code);
        }
    }
}
