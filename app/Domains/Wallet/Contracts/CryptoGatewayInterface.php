<?php

namespace App\Domains\Wallet\Contracts;

interface CryptoGatewayInterface
{
    public function getBalance(string $identifier): float;

    public function generateAddress(\App\Domains\Wallet\Models\Currency $currency, ?\App\Domains\Wallet\Models\HdWallet $hdWallet, ?\App\Domains\Wallet\Models\SystemWallet $gasWallet = null): array;

    public function getTransactionDetails(string $txHash, int $currency_id): array;

    public function isTransactionConfirmed(string $txHash, int $currency_id): bool;
}
