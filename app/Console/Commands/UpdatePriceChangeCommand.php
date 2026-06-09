<?php

namespace App\Console\Commands;

use App\Domains\Wallet\Actions\UpdatePriceChangeAction;
use Illuminate\Console\Command;

class UpdatePriceChangeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:update-price-change';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update 24h price change percentage for all currencies using CoinGecko';

    /**
     * Execute the console command.
     */
    public function handle(UpdatePriceChangeAction $action): void
    {
        $this->info('Updating price changes from CoinGecko...');
        $action->execute();
        $this->info('Price changes updated successfully.');
    }
}
