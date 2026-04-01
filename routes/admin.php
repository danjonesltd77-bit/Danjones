<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->name('admin.')->prefix('admin')->group(function () {
    Route::view('/', 'admin.dashboard')->name('dashboard');
});