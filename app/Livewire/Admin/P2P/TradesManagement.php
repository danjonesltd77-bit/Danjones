<?php

namespace App\Livewire\Admin\P2P;

use App\Domains\P2P\Models\P2PTrade;
use App\Enum\TradeStatus;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TradesManagement extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public ?int $adId = null;

    public function updating()
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'adId']);
    }

    #[Computed]
    public function trades()
    {
        return P2PTrade::query()
            ->with(['seller', 'buyer', 'currency', 'advertisement'])
            ->when($this->search, function ($q) {
                $q->whereHas('seller', fn($sq) => $sq->where('name', 'like', '%' . $this->search . '%'))
                  ->orWhereHas('buyer', fn($sq) => $sq->where('name', 'like', '%' . $this->search . '%'))
                  ->orWhere('fiat_amount', 'like', '%' . $this->search . '%');
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->adId, fn($q) => $q->where('advertisement_id', $this->adId))
            ->latest()
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.admin.p2p.trades-management')->layout('layouts.app');
    }
}
