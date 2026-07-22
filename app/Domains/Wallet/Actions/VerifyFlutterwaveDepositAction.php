<?php

namespace App\Domains\Wallet\Actions;

use App\Domains\Wallet\Contracts\MarketDataGatewayInterface;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\Transaction;
use App\Domains\Wallet\Services\FlutterwaveService;
use App\Domains\Wallet\Services\LedgerService;
use App\Mail\Wallet\DepositReceivedMail;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class VerifyFlutterwaveDepositAction
{
    public function __construct(
        protected FlutterwaveService $flutterwaveService,
        protected LedgerService $ledgerService,
        protected MarketDataGatewayInterface $marketDataGateway
    ) {}

    /**
     * Verify and process a Flutterwave deposit.
     *
     * @throws Exception
     */
    public function execute(User $user, string $reference): array
    {
        // 1. Idempotency Check
        $existing = Transaction::where('reference', $reference)
            ->where('action', 'deposit')
            ->first();

        if ($existing) {
            return [
                'success' => true,
                'message' => 'Transaction already processed.',
                'transaction' => $existing,
            ];
        }

        // 2. Verify with Flutterwave
        $flutterwaveData = $this->flutterwaveService->verifyTransactionByReference($reference);

        // 3. Validate Transaction Data
        if ($flutterwaveData['status'] !== 'successful') {
            throw new Exception("Transaction status is {$flutterwaveData['status']}, expected successful.");
        }

        if ($flutterwaveData['currency'] !== 'NGN') {
            throw new Exception("Transaction currency is {$flutterwaveData['currency']}, expected NGN.");
        }

        $amount = (float) $flutterwaveData['amount'];

        // 4. Get User's Naira Wallet
        $currency = Currency::where('symbol', 'NGN')->first();
        if (! $currency) {
            throw new Exception('Naira currency not configured in the system.');
        }

        $wallet = $user->wallet($currency->id);
        if (! $wallet) {
            throw new Exception('Naira wallet not found for the user.');
        }

        // 5. Process Ledger and Update Balance
        return DB::transaction(function () use ($user, $wallet, $amount, $reference, $flutterwaveData) {
            $usdNgnRate = $this->marketDataGateway->getUsdNgnRate();
            $usdAmount = $amount / ($usdNgnRate ?: 1);

            $this->ledgerService->recordDeposit(
                null, // No system wallet for now unless specified
                $wallet,
                $amount,
                $usdAmount,
                $reference,
                "Flutterwave Deposit: {$reference}",
                $flutterwaveData,
                'completed'
            );

            $transaction = Transaction::where('reference', $reference)
                ->where('wallet_id', $wallet->id)
                ->first();

            if ($transaction) {
                Mail::to($user->email)->queue(new DepositReceivedMail($transaction));
            }

            send_notification($user, 'Deposit Received', 'Your deposit of '.crypto_format($amount).' NGN was successful.', 'deposit_received', ['transaction_id' => $transaction?->id, 'amount' => $amount, 'currency' => 'NGN']);

            return [
                'success' => true,
                'message' => 'Deposit confirmed successfully.',
                'amount' => $amount,
                'transaction' => $transaction,
            ];
        });
    }
}
