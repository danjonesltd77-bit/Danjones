<?php

use App\Livewire\Admin\Dashboard\Dashboard;
use App\Livewire\Admin\Roles\RolesPermissionsManagement;
use App\Livewire\Admin\Transactions\TransactionsManagement;
use App\Livewire\Admin\Users\UsersManagement;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->name('admin.')->prefix('admin')->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    Route::prefix('users')->group(function () {
        Route::get('', UsersManagement::class)->name('users.index');
    });

    Route::prefix('transactions')->group(function () {
        Route::get('', TransactionsManagement::class)->name('transactions.index');
    });

    Route::prefix('roles')->group(function () {
        Route::get('', RolesPermissionsManagement::class)->name('roles.index');
    });
});
