<?php

namespace App\Domains\Wallet\Jobs;

use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Models\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateAddressBalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected ?Wallet $wallet = null) {}

    /**
     * Execute the job.
     */
    public function handle(CryptoGatewayInterface $cryptoGateway): void
    {
        if ($this->wallet) {
            $this->updateWalletBalance($this->wallet, $cryptoGateway);

            return;
        }

        // Global scan for all crypto wallets with addresses
        Wallet::whereNotNull('address')
            ->whereHas('currency', fn ($q) => $q->where('is_crypto', true))
            ->chunkById(100, function ($wallets) use ($cryptoGateway) {
                foreach ($wallets as $wallet) {
                    /** @var Wallet $wallet */
                    $this->updateWalletBalance($wallet, $cryptoGateway);

                    // Small delay to avoid hitting rate limits too hard during global scan
                    usleep(100000); // 100ms
                }
            });
    }

    protected function updateWalletBalance(Wallet $wallet, CryptoGatewayInterface $cryptoGateway): void
    {
        try {
            $balance = $cryptoGateway->getBalance($wallet->address, $wallet->currency);

            $wallet->update([
                'address_balance' => $balance,
            ]);

            // Log::debug("Updated address balance for wallet {$wallet->id}");
        } catch (\Exception $e) {
            Log::error("Failed to update address balance for wallet {$wallet->id}: ".$e->getMessage());
        }
    }
}
