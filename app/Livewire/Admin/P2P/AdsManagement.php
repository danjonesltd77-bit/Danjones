<?php

namespace App\Livewire\Admin\P2P;

use App\Domains\P2P\Models\P2PAdvertisement;
use App\Domains\Wallet\Models\Currency;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AdsManagement extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $type = '';

    #[Url]
    public ?int $currencyId = null;

    #[Url]
    public string $status = '';

    public function updating()
    {
        $this->resetPage();
    }

    public function toggleStatus(int $adId)
    {
        $ad = P2PAdvertisement::findOrFail($adId);
        $ad->update(['is_active' => !$ad->is_active]);

        toast($ad->is_active ? 'Advertisement enabled successfully.' : 'Advertisement disabled successfully.', 'success');

        return $this->redirect(route('admin.p2p.ads.index'), navigate: false);
    }

    #[Computed]
    public function ads()
    {
        return P2PAdvertisement::query()
            ->with(['user', 'currency'])
            ->withCount('trades')
            ->when($this->search, function ($q) {
                $q->whereHas('user', fn($sq) => $sq->where('name', 'like', '%' . $this->search . '%'))
                  ->orWhere('total_amount', 'like', '%' . $this->search . '%');
            })
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->currencyId, fn($q) => $q->where('currency_id', $this->currencyId))
            ->when($this->status !== '', fn($q) => $q->where('is_active', $this->status === 'active'))
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function currencies()
    {
        return Currency::all();
    }

    public function render()
    {
        return view('livewire.admin.p2p.ads-management')->layout('layouts.app');
    }
}
