<?php

namespace App\Domains\Wallet\Gateways;

use App\Domains\Wallet\Contracts\CryptoGatewayInterface;

class TatumCryptoGateway implements CryptoGatewayInterface
{
    public function getBalance(string $identifier): float
    {
        // TODO: Implement actual Tatum API logic
        return 0.0;
    }
}
