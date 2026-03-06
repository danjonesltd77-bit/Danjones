<?php

namespace App\Domains\Wallet\Contracts;

interface WalletAccountInterface
{
    public function getWalletId(): string;
    
    public function getLedgerBalance(): float;
}
