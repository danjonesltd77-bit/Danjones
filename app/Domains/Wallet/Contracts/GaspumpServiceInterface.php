<?php

namespace App\Domains\Wallet\Contracts;

use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\HdWallet;
use App\Domains\Wallet\Models\SystemWallet;

interface GaspumpServiceInterface
{
    // public function transfer(WalletAccountInterface $from, WalletAccountInterface $to, float $amount, Currency $currency): string;

    /**
     * @param  string[]  $recipient_addresses
     * @param  string[]  $amounts
     */
    public function gaspumpBatchTransfer(WalletAccountInterface $from, array $recipient_addresses, array $amounts, SystemWallet $gasWallet, Currency $currency, HdWallet $hdWallet): string;

    public function activateAddress(WalletAccountInterface $wallet, Currency $currency, HdWallet $hdWallet, SystemWallet $gasWallet);

    public function isActivated(WalletAccountInterface $wallet, Currency $currency, ?SystemWallet $gasWallet = null): bool;
}
