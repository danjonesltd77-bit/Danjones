<?php

use App\Livewire\Admin\Currencies\CurrenciesManagement;
use App\Livewire\Admin\Currencies\CurrencyView;
use App\Livewire\Admin\Dashboard\Dashboard;
use App\Livewire\Admin\P2P\AdsManagement;
use App\Livewire\Admin\P2P\TradesManagement;
use App\Livewire\Admin\P2P\TradeView;
use App\Livewire\Admin\Roles\RolesPermissionsManagement;
use App\Livewire\Admin\Transactions\TransactionsManagement;
use App\Livewire\Admin\Transactions\TransactionView;
use App\Livewire\Admin\System\LogViewer;
use App\Livewire\Admin\Users\UsersManagement;
use App\Livewire\Admin\Users\UserView;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->name('admin.')->prefix('admin')->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    Route::prefix('users')->group(function () {
        Route::get('', UsersManagement::class)->name('users.index');
        Route::get('{user}', UserView::class)->name('users.show');
    });

    Route::prefix('transactions')->group(function () {
        Route::get('', TransactionsManagement::class)->name('transactions.index');
        Route::get('{transaction}', TransactionView::class)->name('transactions.show');
    });

    Route::prefix('roles')->group(function () {
        Route::get('', RolesPermissionsManagement::class)->name('roles.index');
    });

    Route::prefix('currencies')->group(function () {
        Route::get('', CurrenciesManagement::class)->name('currencies.index');
        Route::get('{currency}', CurrencyView::class)->name('currencies.show');
    });

    Route::prefix('p2p')->name('p2p.')->group(function () {
        Route::get('ads', AdsManagement::class)->name('ads.index');
        Route::get('trades', TradesManagement::class)->name('trades.index');
        Route::get('trades/{trade}', TradeView::class)->name('trades.show');
    });

    Route::prefix('system')->name('system.')->group(function () {
        Route::get('logs', LogViewer::class)->name('logs');
    });
});
