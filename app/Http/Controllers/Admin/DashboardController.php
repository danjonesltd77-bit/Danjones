<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\Transaction;
use App\Domains\Wallet\Models\Wallet;
use App\Models\User;
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
        // Admin View: Global Statistics
        $totalUsers = User::count();
        $totalWalletsCount = Wallet::count();

        // Fetch global market rates
        $usdNgnRate = $this->marketDataGateway->getUsdNgnRate();

        // Fetch all active crypto currencies to get their prices
        $activeCurrencies = Currency::where('is_active', true)
            ->where('is_crypto', true)
            ->get();

        $prices = $activeCurrencies->mapWithKeys(function ($currency) {
            try {
                return [$currency->id => $this->marketDataGateway->getExchangeRate($currency->id)];
            } catch (\Exception $e) {
                return [$currency->id => 0.0];
            }
        });

        // Calculate Total Platform Balance (USD)
        // We do this by summing balances per currency to minimize gateway calls if we had many wallets
        // But for accuracy and simplicity in this small scale, we can iterate or use a more optimized query
        $totalBalanceUsd = 0;

        // Group wallets by currency to calculate total balance per asset
        $walletSums = Wallet::groupBy('currency_id')
            ->selectRaw('currency_id, sum(balance) as total_balance')
            ->get();

        foreach ($walletSums as $sum) {
            $currency = Currency::find($sum->currency_id);
            if (!$currency) continue;

            $price = $currency->is_crypto
                ? ($prices[$currency->id] ?? 0)
                : 1 / $usdNgnRate;

            $totalBalanceUsd += (float) $sum->total_balance * $price;
        }

        $totalBalanceNgn = $totalBalanceUsd * $usdNgnRate;

        // Fetch Global Recent Transactions
        $recentTransactions = Transaction::with(['wallet.currency'])
            ->latest()
            ->limit(10)
            ->get();

        // Also fetch individual wallet balances for the admin's personal view if needed, 
        // but the user specifically asked for an "Admin" dashboard correction.
        // We'll provide the global stats.

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalWalletsCount' => $totalWalletsCount,
            'totalBalanceUsd' => $totalBalanceUsd,
            'totalBalanceNgn' => $totalBalanceNgn,
            'recentTransactions' => $recentTransactions,
            'usdNgnRate' => $usdNgnRate,
        ]);
    }
}
