<?php

use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showVerificationStep = false;

    public bool $setupComplete = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    /**
     * Mount the component.
     */
    public function mount(bool $requiresConfirmation): void
    {
        $this->requiresConfirmation = $requiresConfirmation;
    }

    #[On('start-two-factor-setup')]
    public function startTwoFactorSetup(): void
    {
        $enableTwoFactorAuthentication = app(EnableTwoFactorAuthentication::class);
        $enableTwoFactorAuthentication(auth()->user());

        $this->loadSetupData();
    }

    /**
     * Load the two-factor authentication setup data for the user.
     */
    private function loadSetupData(): void
    {
        $user = auth()->user()?->fresh();

        try {
            if (! $user || ! $user->two_factor_secret) {
                throw new Exception('Two-factor setup secret is not available.');
            }

            $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError('setupData', 'Failed to fetch setup data.');

            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }

    /**
     * Show the two-factor verification step if necessary.
     */
    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;

            $this->resetErrorBag();

            return;
        }

        $this->closeModal();
        $this->dispatch('two-factor-enabled');
    }

    /**
     * Confirm two-factor authentication for the user.
     */
    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate();

        $confirmTwoFactorAuthentication(auth()->user(), $this->code);

        $this->setupComplete = true;

        $this->closeModal();

        $this->dispatch('two-factor-enabled');
    }

    /**
     * Reset two-factor verification state.
     */
    public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');

        $this->resetErrorBag();
    }

    /**
     * Close the two-factor authentication modal.
     */
    public function closeModal(): void
    {
        $this->reset(
            'code',
            'manualSetupKey',
            'qrCodeSvg',
            'showVerificationStep',
            'setupComplete',
        );

        $this->resetErrorBag();
    }

    /**
     * Get the current modal configuration state.
     */
    public function getModalConfigProperty(): array
    {
        if ($this->setupComplete) {
            return [
                'title' => __('Two-factor authentication enabled'),
                'description' => __('Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.'),
                'buttonText' => __('Close'),
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => __('Verify authentication code'),
                'description' => __('Enter the 6-digit code from your authenticator app.'),
                'buttonText' => __('Continue'),
            ];
        }

        return [
            'title' => __('Enable two-factor authentication'),
            'description' => __('To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app.'),
            'buttonText' => __('Continue'),
        ];
    }
}; ?>

<flux:modal
    name="two-factor-setup-modal"
    class="max-w-md"
    @close="closeModal"
>
    <div class="space-y-8 py-2">
        <!-- Progress Indicator -->
        <div class="flex items-center justify-center space-x-3 mb-4">
            <div class="flex items-center justify-center w-8 h-8 rounded-full {{ !$showVerificationStep ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-success text-white' }} text-xs font-bold transition-all duration-500">
                @if($showVerificationStep) <i data-lucide="check" class="w-4 h-4"></i> @else 1 @endif
            </div>
            <div class="w-12 h-px {{ $showVerificationStep ? 'bg-success' : 'bg-slate-200 dark:bg-darkmode-400' }} transition-colors duration-500"></div>
            <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $showVerificationStep ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-slate-100 dark:bg-darkmode-400 text-slate-400' }} text-xs font-bold transition-all duration-500">
                2
            </div>
        </div>

        <div class="text-center space-y-2">
            <flux:heading size="lg" class="tracking-tight">{{ $this->modalConfig['title'] }}</flux:heading>
            <flux:subheading class="px-6">{{ $this->modalConfig['description'] }}</flux:subheading>
        </div>

        @if ($showVerificationStep)
            <!-- Step 2: Verification -->
            <div class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-500">
                <div class="flex flex-col items-center justify-center space-y-6">
                    <div class="w-20 h-20 rounded-2xl bg-primary/5 border border-primary/10 flex items-center justify-center text-primary">
                        <i data-lucide="smartphone" class="w-10 h-10 animate-bounce"></i>
                    </div>
                    
                    <flux:input
                        name="code"
                        wire:model="code"
                        placeholder="000000"
                        maxlength="6"
                        class="text-center text-2xl tracking-[0.5em] font-bold !bg-slate-50 dark:!bg-darkmode-600 border-none h-16 w-full max-w-[240px] focus:ring-primary shadow-inner"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <flux:button
                        variant="ghost"
                        class="font-bold text-xs uppercase tracking-widest"
                        wire:click="resetVerification"
                    >
                        {{ __('Back') }}
                    </flux:button>

                    <flux:button
                        variant="primary"
                        class="font-bold text-xs uppercase tracking-widest shadow-lg shadow-primary/20"
                        wire:click="confirmTwoFactor"
                        x-bind:disabled="$wire.code.length < 6"
                    >
                        {{ __('Confirm & Enable') }}
                    </flux:button>
                </div>
            </div>
        @else
            <!-- Step 1: Scan QR Code -->
            <div class="space-y-8 animate-in fade-in zoom-in-95 duration-500">
                @error('setupData')
                    <div class="p-4 rounded-xl bg-danger/10 border border-danger/20 flex items-center space-x-3 text-danger">
                        <i data-lucide="x-circle" class="w-5 h-5"></i>
                        <span class="text-xs font-bold uppercase tracking-widest">{{ $message }}</span>
                    </div>
                @enderror

                <!-- QR Code Display -->
                <div class="flex justify-center">
                    <div class="relative group p-4 bg-white dark:bg-white rounded-3xl shadow-2xl border border-slate-100 dark:border-transparent transition-transform hover:scale-105 duration-500">
                        @empty($qrCodeSvg)
                            <div class="w-48 h-48 flex items-center justify-center">
                                <flux:icon.loading class="text-primary"/>
                            </div>
                        @else
                            <div class="w-48 h-48 flex items-center justify-center p-2">
                                {!! $qrCodeSvg !!}
                            </div>
                            <!-- Decorative Corner Brackets -->
                            <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-primary/20 rounded-tl-3xl"></div>
                            <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-primary/20 rounded-tr-3xl"></div>
                            <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-primary/20 rounded-bl-3xl"></div>
                            <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-primary/20 rounded-br-3xl"></div>
                        @endempty
                    </div>
                </div>

                <!-- Manual Entry Option -->
                <div class="space-y-4">
                    <div class="relative flex items-center justify-center w-full">
                        <div class="absolute inset-0 w-full h-px top-1/2 bg-slate-200 dark:bg-darkmode-400"></div>
                        <span class="relative px-3 text-[10px] font-bold uppercase tracking-[0.2em] bg-white dark:bg-darkmode-600 text-slate-400 italic">
                            {{ __('Manual Setup Key') }}
                        </span>
                    </div>

                    <div x-data="{ copied: false }" class="relative group">
                        <input
                            type="text"
                            readonly
                            value="{{ $manualSetupKey }}"
                            class="w-full h-12 pl-4 pr-12 bg-slate-50 dark:bg-darkmode-400 border border-slate-200 dark:border-darkmode-400 rounded-xl text-center font-mono text-sm tracking-widest text-slate-700 dark:text-slate-300 focus:outline-none transition-all group-hover:border-primary/30"
                        />
                        <button
                            @click="
                                navigator.clipboard.writeText('{{ $manualSetupKey }}');
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            "
                            type="button"
                            class="absolute right-2 top-2 h-8 w-8 flex items-center justify-center rounded-lg bg-white dark:bg-darkmode-600 shadow-sm border border-slate-100 dark:border-darkmode-400 text-slate-400 hover:text-primary transition-all"
                        >
                            <i x-show="!copied" data-lucide="copy" class="w-4 h-4"></i>
                            <i x-show="copied" data-lucide="check" class="w-4 h-4 text-success animate-in zoom-in duration-300"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <flux:button
                        :disabled="$errors->has('setupData')"
                        variant="primary"
                        class="w-full h-12 font-bold text-xs uppercase tracking-widest shadow-lg shadow-primary/20"
                        wire:click="showVerificationIfNecessary"
                    >
                        {{ __('I have scanned the code') }}
                        <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                    </flux:button>
                </div>
            </div>
        @endif
    </div>
</flux:modal>
