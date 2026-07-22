<?php

namespace App\Domains\Wallet\Actions;

use App\Domains\Wallet\Contracts\CryptoGatewayInterface;
use App\Domains\Wallet\Contracts\GaspumpServiceInterface;
use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Contracts\TransactionRepositoryInterface;
use App\Domains\Wallet\Models\OnchainSend;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Services\LedgerService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminSendAction
{
    public function __construct(
        protected CryptoGatewayInterface $cryptoGateway,
        protected LedgerService $ledgerService,
        protected MarketDataGatewayInterface $marketDataGateway
    ) {}

    /**
     * @throws Exception
     */
    public function execute(SystemWallet $systemWallet, float $amount, string $recipientAddress): array
    {
        $currency = $systemWallet->currency;
        $amount = round($amount, 6);

        if ($systemWallet->balance < $amount) {
            throw new Exception("Insufficient balance in the {$systemWallet->type->label()} wallet.");
        }

        $rate = $this->marketDataGateway->getExchangeRate($currency->id);

        return DB::transaction(function () use ($systemWallet, $currency, $amount, $recipientAddress, $rate) {
            // Re-fetch and lock for update
            $lockedWallet = SystemWallet::where('id', $systemWallet->id)->lockForUpdate()->firstOrFail();

            if ($lockedWallet->balance < $amount) {
                throw new Exception('Insufficient balance during processing.');
            }

            // Perform On-chain Transaction
            if ($currency->is_gaspump) {
                $res = $this->handleGaspumpSend($currency, $lockedWallet, $amount, $recipientAddress);
            } else {
                // For UTXO, we use the standard utxoSend which pools from system change + user wallets.
                // In an admin context, we specify 0 fee (or let gateway estimate)
                // Note: TatumCryptoGateway::utxoSend estimates fee internally if needed or takes it as arg.
                $networkFee = 0; // Admin sends might still need network fees, but we'll assume the gateway handles it or we deduct from balance.
                // Actually, let's estimate the fee first.
                $networkFee = $this->cryptoGateway->estimateOnchainFee($currency, $amount);

                if ($lockedWallet->balance < ($amount + $networkFee)) {
                    throw new Exception('Insufficient balance to cover amount plus network fees ('.crypto_format($networkFee, $currency->decimal).').');
                }

                $res = $this->cryptoGateway->utxoSend($currency, $recipientAddress, $amount, $networkFee);
            }

            $txId = $res['txId'] ?? $res['signatureId'] ?? Str::random(16);
            $totalDebit = $amount + ($networkFee ?? 0);

            // Record Ledger Entry (Debit system wallet)
            // We use recordWithdrawal but since it expects a User Wallet, we'll call repository directly or update LedgerService.
            // Actually, we can just call recordWithdrawal with null for system wallet? No, that's backwards.
            // Let's create a recordSystemDebit in LedgerService or call repository.
            // I'll call the repository via a new method in LedgerService or just use recordWithdrawal logic.

            // I'll manually record it for now to stay safe with existing LedgerService methods.
            // Actually, I'll add recordSystemMovement to LedgerService.

            $lockedWallet->decrement('balance', $totalDebit);

            // Record the transaction
            app(TransactionRepositoryInterface::class)->recordEntry(
                $lockedWallet,
                $totalDebit,
                $totalDebit * $rate,
                'debit',
                'withdrawal',
                $txId,
                "Admin transfer to {$recipientAddress}",
                [],
                'completed'
            );

            // Record Audit Entry
            OnchainSend::create([
                'signatureId' => $res['signatureId'] ?? null,
                'txid' => $res['txId'] ?? null,
                'sender_address' => [$lockedWallet->address ?: 'SYSTEM_POOL'],
                'recipient_address' => $recipientAddress,
                'amount' => $amount,
                'fee' => $networkFee ?? 0,
                'rate' => $rate,
                'currency_id' => $currency->id,
                'status' => 'pending',
            ]);

            return $res;
        });
    }

    protected function handleGaspumpSend($currency, $fromWallet, $amount, $recipientAddress): array
    {
        $gaspump = app(GaspumpServiceInterface::class);

        $gasCurrencyId = $currency->parent_id ?: $currency->id;

        // For Gaspump, we need a Gas wallet to pay for the transfer if it's not the gas wallet itself.
        $gasWallet = SystemWallet::where('currency_id', $gasCurrencyId)
            ->where('type', \App\Enum\SystemWalletType::GAS)
            ->first();

        if (! $gasWallet) {
            throw new Exception('Gas wallet not configured for this currency.');
        }

        $txId = $gaspump->gaspumpBatchTransfer(
            $fromWallet,
            [$recipientAddress],
            [(string) $amount],
            $gasWallet,
            $currency,
            $currency->hdWallet
        );

        return ['txId' => $txId];
    }
}
