<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('external/{id}', function ($id) {
    return response()->json(true);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('super-admin')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('home');
    })->name('dashboard');
});

require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
