<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\RolesPermissionsManagement;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->name('admin.')->prefix('admin')->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');




    
    Route::get('roles', RolesPermissionsManagement::class)->name('roles.index');
});