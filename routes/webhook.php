<?php

use Illuminate\Support\Facades\Route;

Route::prefix('tatum')->name('tatum.webhook.')->group(function () {
    Route::post('/receive', [TatumSubscriptionController::class, 'recieveSubscriptionInfo'])->name('incoming');
});
