<?php

use App\Domains\Wallet\Jobs\UpdateAddressBalanceJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('wallet:scan-balances', function () {
    $this->info('Dispatching UpdateAddressBalanceJob...');
    UpdateAddressBalanceJob::dispatch();
    $this->info('Job dispatched successfully.');
})->purpose('Scan and update all crypto wallet on-chain balances');

Schedule::command('wallet:check-pending-deposits')->everyMinute();
Schedule::command('p2p:cancel-expired-trades')->everyMinute();
Schedule::job(new UpdateAddressBalanceJob)->daily();
