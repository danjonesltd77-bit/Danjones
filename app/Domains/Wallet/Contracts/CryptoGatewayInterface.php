<?php

namespace App\Domains\Wallet\Contracts;

use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\HdWallet;
use App\Domains\Wallet\Models\SystemWallet;

interface CryptoGatewayInterface
{
    public function getBalance(string $address, Currency $currency): float;

    public function generateAddress(Currency $currency, HdWallet $hdWallet, ?string $chain = null, ?SystemWallet $gasWallet = null): string;

    public function getTransactionDetails(string $txHash, Currency $currency): array;

    public function isTransactionConfirmed(string $txHash, Currency $currency): bool;

    public function estimateOnchainFee(Currency $currency, float $amount): float;

    public function utxoSend(Currency $currency, array $formattedWallets, string $to, float $amount, float $fee, ?string $changeAddress = null): array;
}
