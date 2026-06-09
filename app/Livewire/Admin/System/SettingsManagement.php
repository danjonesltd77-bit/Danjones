<?php

namespace App\Livewire\Admin\System;

use App\Domains\Core\Models\Setting;
use Livewire\Component;

class SettingsManagement extends Component
{
    public $search = '';

    public $activeTab = 'all'; // 'cashback', 'partner', 'general', 'all'

    public $showCreateModal = false;

    // Fields for creating a new setting
    public $newKey = '';

    public $newValue = '';

    public $newType = 'float';

    public $newDescription = '';

    // Fields for editing
    public $editingSettingId = null;

    public $editingValue = '';

    protected $rules = [
        'newKey' => 'required|string|unique:settings,key|max:255',
        'newValue' => 'required|string',
        'newType' => 'required|string|in:string,integer,float,boolean',
        'newDescription' => 'nullable|string|max:500',
    ];

    public function mount()
    {
        // Require authorization
        if (! auth()->user()->can('manage settings')) {
            abort(403);
        }
    }

    public function startEdit($id)
    {
        $setting = Setting::findOrFail($id);
        $this->editingSettingId = $id;
        $this->editingValue = $setting->value;
    }

    public function cancelEdit()
    {
        $this->editingSettingId = null;
        $this->editingValue = '';
    }

    public function saveEdit($id)
    {
        $setting = Setting::findOrFail($id);

        // Validate based on setting type
        if ($setting->type === 'float') {
            if (! is_numeric($this->editingValue)) {
                $this->dispatch('toast', message: 'Value must be a valid float number.', type: 'error');

                return;
            }
        } elseif ($setting->type === 'integer') {
            if (! filter_var($this->editingValue, FILTER_VALIDATE_INT) && $this->editingValue !== '0') {
                $this->dispatch('toast', message: 'Value must be a valid integer.', type: 'error');

                return;
            }
        } elseif ($setting->type === 'boolean') {
            $val = strtolower($this->editingValue);
            if (! in_array($val, ['1', '0', 'true', 'false'])) {
                $this->dispatch('toast', message: 'Value must be boolean (1, 0, true, false).', type: 'error');

                return;
            }
        }

        $setting->update([
            'value' => $this->editingValue,
        ]);

        $this->editingSettingId = null;
        $this->editingValue = '';

        $this->dispatch('toast', message: "Setting '{$setting->key}' updated successfully!", type: 'success');
    }

    public function openCreateModal()
    {
        $this->reset(['newKey', 'newValue', 'newType', 'newDescription']);
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function createSetting()
    {
        $this->validate();

        Setting::create([
            'key' => $this->newKey,
            'value' => $this->newValue,
            'type' => $this->newType,
            'description' => $this->newDescription,
        ]);

        $this->showCreateModal = false;
        $this->dispatch('toast', message: 'New setting key registered successfully!', type: 'success');
    }

    public function deleteSetting($id)
    {
        $setting = Setting::findOrFail($id);
        $setting->delete();
        $this->dispatch('toast', message: 'Setting deleted successfully!', type: 'success');
    }

    public function render()
    {
        $query = Setting::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('key', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->activeTab === 'cashback') {
            $query->where('key', 'like', 'cashback_%');
        } elseif ($this->activeTab === 'partner') {
            $query->where('key', 'like', 'partner_%');
        } elseif ($this->activeTab === 'general') {
            $query->where('key', 'not like', 'cashback_%')
                ->where('key', 'not like', 'partner_%');
        }

        $settings = $query->orderBy('key')->get();

        return view('livewire.admin.system.settings-management', [
            'settings' => $settings,
        ])->layout('layouts.app');
    }
}
