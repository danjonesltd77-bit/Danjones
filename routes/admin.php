<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Livewire\Admin\RolesPermissionsManagement;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->name('admin.')->prefix('admin')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');




    
    Route::get('roles', RolesPermissionsManagement::class)->name('roles.index');
});