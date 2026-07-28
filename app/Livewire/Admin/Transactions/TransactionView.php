<?php

namespace App\Livewire\Admin\Transactions;

use App\Domains\Wallet\Models\Transaction;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TransactionView extends Component
{
    public Transaction $transaction;

    public function mount(Transaction $transaction)
    {
        $this->transaction = $transaction->load(['user', 'wallet.currency', 'currency']);
    }

    #[Computed]
    public function relatedTransactions()
    {
        $transactions = Transaction::query()
            ->with(['currency', 'user'])
            ->where('reference', $this->transaction->reference)
            ->where('id', '!=', $this->transaction->id)
            ->get();

        return [
            'system' => $transactions->filter(fn ($tx) => $tx->user_id === 0),
            'user' => $transactions->filter(fn ($tx) => $tx->user_id > 0),
        ];
    }

    public function revert()
    {
        abort_unless(auth()->user()->can('revert transactions'), 403);

        try {
            $revertAction = app(\App\Domains\Wallet\Actions\RevertFailedOnchainSendAction::class);
            $revertAction->execute($this->transaction);
            $this->transaction->refresh();

            session()->flash('toast', [
                'type' => 'success',
                'message' => 'Transaction has been successfully reverted.',
            ]);

            return redirect(route('admin.transactions.show', $this->transaction));
        } catch (\Exception $e) {
            session()->flash('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.transactions.transaction-view');
    }
}
