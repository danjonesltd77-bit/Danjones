<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/transaction-pin', [AuthController::class, 'setTransactionPin']);
    Route::post('/update-transaction-pin', [AuthController::class, 'updateTransactionPin']);

    Route::prefix('wallets')->group(function () {
        Route::get('/currencies', [WalletController::class, 'currencies']);
        Route::get('/', [WalletController::class, 'wallets']);
        Route::post('/create', [WalletController::class, 'create']);
    });
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

require __DIR__ . '/webhook.php';
