<?php

namespace App\Domains\Wallet\Contracts;

interface WalletAccountInterface
{
    public function getWalletId(): string;
    public function getUserId(): int;
    public function getCurrencyId(): int;
    public function getLedgerBalance(): float;
}
