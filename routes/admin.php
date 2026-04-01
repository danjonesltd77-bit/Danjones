<?php

use App\Livewire\Admin\RolesPermissionsManagement;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->name('admin.')->prefix('admin')->group(function () {
    Route::view('/', 'admin.dashboard')->name('dashboard');
    Route::get('roles', RolesPermissionsManagement::class)->name('roles.index');
});