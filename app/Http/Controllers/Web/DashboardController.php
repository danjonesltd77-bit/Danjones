<?php

namespace App\Http\Controllers\Web;

use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected MarketDataGatewayInterface $marketDataGateway
    ) {}

    public function index(Request $request): View
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

        // Fetch global USD/NGN rate
        $usdNgnRate = $this->marketDataGateway->getUsdNgnRate();

        // Calculate wallet balances in USD and gather recent transactions
        $recentTransactions = collect();

        $totalBalanceUsd = $user->wallets->sum(function ($wallet) use ($prices, $usdNgnRate, &$recentTransactions) {
            $price = $wallet->currency->is_crypto
                ? ($prices[$wallet->currency_id] ?? 0)
                : 1 / $usdNgnRate;

            $wallet->balance_usd = (float) $wallet->balance * $price;

            // Collect transactions from each wallet
            $recentTransactions = $recentTransactions->concat($wallet->transactions()->limit(5)->get());

            return $wallet->balance_usd;
        });

        $totalBalanceNgn = $totalBalanceUsd * $usdNgnRate;

        // Sort combined transactions by latest
        $recentTransactions = $recentTransactions->sortByDesc('created_at')->take(10);

        return view('admin.dashboard', [
            'user' => $user,
            'wallets' => $user->wallets,
            'totalBalanceUsd' => $totalBalanceUsd,
            'totalBalanceNgn' => $totalBalanceNgn,
            'recentTransactions' => $recentTransactions,
            'usdNgnRate' => $usdNgnRate,
        ]);
    }
}
