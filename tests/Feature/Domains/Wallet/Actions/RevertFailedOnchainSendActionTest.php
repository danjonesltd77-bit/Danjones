<?php

namespace Tests\Feature\Domains\Wallet\Actions;

use App\Domains\Wallet\Actions\RevertFailedOnchainSendAction;
use App\Domains\Wallet\Models\Currency;
use App\Domains\Wallet\Models\OnchainSend;
use App\Domains\Wallet\Models\SystemWallet;
use App\Domains\Wallet\Models\Transaction;
use App\Domains\Wallet\Models\Wallet;
use App\Domains\Wallet\Services\LedgerService;
use App\Enum\SystemWalletType;
use App\Enum\TransactionStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevertFailedOnchainSendActionTest extends TestCase
{
    use RefreshDatabase;

    protected LedgerService $ledgerService;

    protected RevertFailedOnchainSendAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ledgerService = app(LedgerService::class);
        $this->action = app(RevertFailedOnchainSendAction::class);
    }

    public function test_revert_action_reverses_withdrawal_and_fee_and_updates_balances_and_statuses()
    {
        $user = User::factory()->create();
        $currency = Currency::factory()->create([
            'symbol' => 'ETH',
            'token_currency' => 'ETH',
            'decimal' => 18,
            'fee' => 0.005,
        ]);

        $userWallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'currency_id' => $currency->id,
            'balance' => 100.0,
        ]);

        $systemFeeWallet = SystemWallet::factory()->create([
            'currency_id' => $currency->id,
            'type' => SystemWalletType::FEE,
            'balance' => 0.0,
        ]);

        $reference = 'tx_mock_ref_123';

        // 1. Record withdrawal of 10 ETH + 0.1 network fee
        $withdrawalTx = $this->ledgerService->recordWithdrawal(
            $userWallet,
            null,
            10.1,
            10.1 * 2000.0,
            $reference,
            'Withdrawal to external address'
        );

        // 2. Record service fee of 0.5 ETH
        $this->ledgerService->recordFee(
            $userWallet,
            $systemFeeWallet,
            0.5,
            0.5 * 2000.0,
            $reference,
            'Service fee for withdrawal'
        );

        // 3. Record pending OnchainSend
        $onchainSend = OnchainSend::create([
            'signatureId' => $reference,
            'txid' => $reference,
            'sender_address' => ['0xuser_addr'],
            'recipient_address' => '0xrecipient_addr',
            'amount' => 10.0,
            'fee' => 0.6,
            'rate' => 2000.0,
            'currency_id' => $currency->id,
            'status' => 'pending',
        ]);

        // Refresh user and fee wallet to check balances post-withdrawal
        $userWallet->refresh();
        $systemFeeWallet->refresh();

        $this->assertEquals(89.4, $userWallet->balance); // 100.0 - 10.1 - 0.5 = 89.4
        $this->assertEquals(0.5, $systemFeeWallet->balance);

        // 4. Run the RevertFailedOnchainSendAction
        $this->action->execute($withdrawalTx);

        // Refresh user and fee wallet to check balances post-reversal
        $userWallet->refresh();
        $systemFeeWallet->refresh();

        // Balances should be fully refunded / debited back
        $this->assertEquals(100.0, $userWallet->balance);
        $this->assertEquals(0.0, $systemFeeWallet->balance);

        // Original transactions should be status 'refunded'
        $originalTxs = Transaction::where('reference', $reference)->get();
        $this->assertCount(3, $originalTxs);
        foreach ($originalTxs as $tx) {
            $this->assertEquals(TransactionStatus::REFUNDED, $tx->status);
        }

        // Reversal refund entries should exist
        $refundTxs = Transaction::where('reference', 'refund_'.$reference)->get();
        $this->assertCount(3, $refundTxs); // 1 user withdrawal refund, 1 user fee refund, 1 system fee reversal
        foreach ($refundTxs as $tx) {
            $this->assertEquals('refund', $tx->action->value);
            $this->assertEquals(TransactionStatus::COMPLETED, $tx->status);
        }

        // OnchainSend record status should be 'failed'
        $onchainSend->refresh();
        $this->assertEquals('failed', $onchainSend->status);
    }

    public function test_revert_action_throws_exception_if_already_reverted()
    {
        $user = User::factory()->create();
        $currency = Currency::factory()->create();
        $userWallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'currency_id' => $currency->id,
            'balance' => 100.0,
        ]);

        $reference = 'tx_mock_ref_456';

        $withdrawalTx = $this->ledgerService->recordWithdrawal(
            $userWallet,
            null,
            10.0,
            10.0 * 2000.0,
            $reference,
            'Withdrawal to external address'
        );

        // Simulate that transaction is already refunded
        $withdrawalTx->update(['status' => TransactionStatus::REFUNDED]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This transaction has already been reverted or refunded.');

        $this->action->execute($withdrawalTx);
    }
}
