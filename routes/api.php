<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BankAccountController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\P2PController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/user', [DashboardController::class, 'user']);

    Route::post('/transaction-pin', [AuthController::class, 'setTransactionPin']);
    Route::post('/update-transaction-pin', [AuthController::class, 'updateTransactionPin']);
    Route::post('/verify-transaction-pin', [AuthController::class, 'verifyTransactionPin']);

    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

    Route::prefix('wallets')->group(function () {
        Route::get('/', [WalletController::class, 'wallets']);
        Route::get('/rates', [WalletController::class, 'rates']);
        Route::get('/currencies', [WalletController::class, 'currencies']);
        Route::get('/wallet/{currencyId}', [WalletController::class, 'wallet']);
        Route::post('/create', [WalletController::class, 'create']);
        Route::post('/sell', [WalletController::class, 'sell']);
        Route::get('/send-fee', [WalletController::class, 'sendFee']);
        Route::post('/send', [WalletController::class, 'send']);
        Route::post('/deposit/flutterwave/verify', [WalletController::class, 'verifyFlutterwaveDeposit']);
    });
    Route::prefix('bank-accounts')->controller(BankAccountController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/list', 'bankList');
        Route::post('/store', 'store');
        Route::get('/delete/{bankAccount}', 'destroy');
    });

    Route::prefix('p2p')->controller(P2PController::class)->group(function () {
        Route::get('/ads', 'indexAds');
        Route::get('/my-ads', 'myAds');
        Route::get('/my-trades', 'myTrades');
        Route::post('/close-ads/{ad}', 'closeAd');
        Route::post('/create-ads', 'storeAd');

        Route::post('/initiate-trade', 'initiateTrade');
        Route::post('/trades/{trade}/pay', 'markTradePaid');
        Route::post('/trades/{trade}/complete', 'completeTrade');
        Route::post('/trades/{trade}/cancel', 'cancelTrade');
        Route::post('/trades/{trade}/dispute', 'disputeTrade');
    });

    Route::prefix('verifications')->controller(VerificationController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/verify-nin', 'verifyNin');
    });
});

require __DIR__.'/webhook.php';
