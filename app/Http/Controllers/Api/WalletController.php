<?php

namespace App\Http\Controllers\Api;

use App\Domains\Wallet\Models\Currency;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Return all active currencies.
     */
    public function currencies()
    {
        $currencies = Currency::where('is_active', true)->get();

        return response()->json([
            'success'    => true,
            'currencies' => $currencies,
        ]);
    }

    /**
     * Return the authenticated user's wallets with their currency.
     */
    public function wallets(Request $request)
    {
        $wallets = $request->user()
            ->wallets()
            ->with('currency')
            ->get();

        return response()->json([
            'success' => true,
            'wallets' => $wallets,
        ]);
    }
}
