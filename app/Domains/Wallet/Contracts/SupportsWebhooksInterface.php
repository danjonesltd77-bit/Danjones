<?php

namespace App\Domains\Wallet\Contracts;

interface SupportsWebhooksInterface
{
    public function subscribeToIncoming(WalletAccountInterface $wallet): bool;
}
