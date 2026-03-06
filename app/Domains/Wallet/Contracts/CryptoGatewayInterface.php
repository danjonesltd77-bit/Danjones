<?php

namespace App\Domains\Wallet\Contracts;

interface CryptoGatewayInterface
{
    public function getBalance(string $identifier): float;

    public function generateAddress(int $currency_id): array;

}
