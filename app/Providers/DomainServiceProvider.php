<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Wallet\Contracts\TransactionRepositoryInterface;
use App\Domains\Wallet\Repositories\EloquentTransactionRepository;
use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Gateways\TatumCryptoGateway;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;

class DomainServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(TransactionRepositoryInterface::class, EloquentTransactionRepository::class);
        $this->app->bind(CryptoGatewayInterface::class, TatumCryptoGateway::class);
        $this->app->bind(MarketDataGatewayInterface::class, TatumCryptoGateway::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
