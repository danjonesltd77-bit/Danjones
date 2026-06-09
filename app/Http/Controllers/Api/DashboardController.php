<?php

namespace App\Http\Controllers\Api;

use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected MarketDataGatewayInterface $marketDataGateway
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        // Ensure wallets and currencies are loaded
        $user->load(['wallets.currency']);

        // Get all active crypto currencies for the "crypto list"
        $activeCurrencies = Currency::where('is_active', true)
            ->where('is_crypto', true)
            ->get();

        // Fetch prices for all active crypto currencies
        $prices = $activeCurrencies->mapWithKeys(function ($currency) {
            try {
                return [$currency->id => $this->marketDataGateway->getExchangeRate($currency->id)];
            } catch (\Exception $e) {
                return [$currency->id => 0.0];
            }
        });

        // Add NGN price (constant 1.0 relative to USD exchange rate handled later)
        // Actually, Tatum returns USD rates.
        $usdNgnRate = $this->marketDataGateway->getUsdNgnRate();

        // Calculate total balance in USD
        $totalBalanceUsd = $user->wallets->sum(function ($wallet) use ($prices, $usdNgnRate) {
            $price = $wallet->currency->is_crypto
                ? ($prices[$wallet->currency_id] ?? 0)
                : 1 / $usdNgnRate;

            $wallet->balance_usd = (float) $wallet->balance * $price;
            $wallet->rate_usd = (float) $price;

            if ($wallet->currency->is_crypto) {
                $changePercentage = ($wallet->currency->price_change_24h ?? 0) / 100;
                $wallet->pnl_24h_amount = $wallet->balance_usd * ($changePercentage / (1 + $changePercentage));
                $wallet->pnl_24h_percentage = (float) ($wallet->currency->price_change_24h ?? 0);
            } else {
                $wallet->pnl_24h_amount = 0.0;
                $wallet->pnl_24h_percentage = 0.0;
            }

            return $wallet->balance_usd;
        });

        $totalBalanceNgn = $totalBalanceUsd * $usdNgnRate;

        $totalPnlUsd = $user->wallets->sum(function ($wallet) {
            if (! $wallet->currency->is_crypto) {
                return 0;
            }

            // price_change_24h is stored as a percentage (e.g., 5.5 for 5.5%), so we divide by 100
            $changePercentage = ($wallet->currency->price_change_24h ?? 0) / 100;

            // PnL = Current Value * (Change % / (1 + Change %))
            // This estimates the value gain/loss of the current holding
            return $wallet->balance_usd * ($changePercentage / (1 + $changePercentage));
        });

        $pnlPercentage = $totalBalanceUsd > 0 && ($totalBalanceUsd - $totalPnlUsd) != 0
            ? ($totalPnlUsd / ($totalBalanceUsd - $totalPnlUsd)) * 100
            : 0;

        return new DashboardResource([
            'user' => $user,
            'wallets' => $user->wallets,
            'total_balance_usd' => $totalBalanceUsd,
            'total_balance_ngn' => $totalBalanceNgn,
            'pnl_24h_amount' => $totalPnlUsd,
            'pnl_24h_percentage' => $pnlPercentage,
        ]);
    }

    public function user()
    {
        $user = auth()->user();

        return response()->json(new UserResource($user));
    }
}
