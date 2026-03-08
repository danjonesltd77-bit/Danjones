<?php

namespace App\Domains\Wallet\Contracts;

interface MarketDataGatewayInterface
{
    /**
     * Get the USD rate for a specific cryptocurrency.
     */
    public function getExchangeRate(int $currencyId): float;
}
