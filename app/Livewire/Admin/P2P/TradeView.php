<?php

namespace App\Livewire\Admin\P2P;

use App\Domains\P2P\Actions\CancelTradeAction;
use App\Domains\P2P\Actions\CompleteTradeAction;
use App\Domains\P2P\Models\P2PTrade;
use App\Enum\TradeStatus;
use Livewire\Component;

class TradeView extends Component
{
    public P2PTrade $trade;

    public function mount(P2PTrade $trade)
    {
        $this->trade = $trade->load(['seller', 'buyer', 'currency', 'advertisement.user']);
    }

    public function releaseCrypto(CompleteTradeAction $action): void
    {
        try {
            $action->execute(auth()->user(), $this->trade, isAdmin: true);
            $this->trade->refresh();
            toast('Crypto released to buyer successfully.', 'success');
        } catch (\Exception $e) {
            toast($e->getMessage(), 'error');
        }
    }

    public function cancelTrade(CancelTradeAction $action): void
    {
        try {
            $action->execute(auth()->user(), $this->trade, isAdmin: true);
            $this->trade->refresh();
            toast('Trade cancelled and funds returned to seller.', 'warning');
        } catch (\Exception $e) {
            toast($e->getMessage(), 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.p2p.trade-view')->layout('layouts.app');
    }
}
