<?php

namespace App\Http\Controllers\Api;

use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
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
            if (!$wallet->currency->is_crypto) {
                // If it's a fiat wallet (like NGN), convert to USD
                return $wallet->balance / $usdNgnRate;
            }
            
            $price = $prices[$wallet->currency_id] ?? 0;
            return $wallet->balance * $price;
        });

        $totalBalanceNgn = $totalBalanceUsd * $usdNgnRate;

        return new DashboardResource([
            'user' => $user,
            'wallets' => $user->wallets,
            'total_balance_usd' => $totalBalanceUsd,
            'total_balance_ngn' => $totalBalanceNgn,
        ]);
    }
}
