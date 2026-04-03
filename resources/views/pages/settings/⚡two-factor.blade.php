<?php

use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;

new #[Title('Two-factor authentication')] class extends Component {
    public bool $twoFactorEnabled;

    public bool $requiresConfirmation;

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        abort_unless(Features::enabled(Features::twoFactorAuthentication()), Response::HTTP_FORBIDDEN);

        if (Fortify::confirmsTwoFactorAuthentication() && is_null(auth()->user()->two_factor_confirmed_at)) {
            $disableTwoFactorAuthentication(auth()->user());
        }

        $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
    }

    /**
     * Handle the two-factor authentication enabled event.
     */
    #[On('two-factor-enabled')]
    public function onTwoFactorEnabled(): void
    {
        $this->twoFactorEnabled = true;
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());

        $this->twoFactorEnabled = false;
    }
} ?>

<section class="w-full">
    <flux:heading class="sr-only">{{ __('Two-factor authentication settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Two-Factor Authentication')" :subheading="__('Add an extra layer of security to your account using TOTP')">
        <div class="mt-8 w-full mx-auto space-y-8" wire:cloak>
            @if ($twoFactorEnabled)
                <!-- Enabled State -->
                <div class="space-y-8">
                    <div class="p-5 rounded-2xl border border-success/20 bg-success/5 flex items-start space-x-5 animate-in zoom-in duration-300">
                        <div class="w-12 h-12 rounded-full bg-success/10 flex items-center justify-center text-success flex-shrink-0 animate-pulse">
                            <i data-lucide="shield-check" class="w-6 h-6"></i>
                        </div>
                        <div class="flex-1 pt-0.5">
                            <div class="flex items-center justify-between">
                                <div class="text-base font-bold text-slate-700 dark:text-slate-300">{{ __('2FA is Active') }}</div>
                                <div class="px-2 py-0.5 bg-success text-white text-[10px] font-bold uppercase tracking-widest rounded shadow-sm shadow-success/20">Secure</div>
                            </div>
                            <div class="text-sm text-slate-500 mt-2 leading-relaxed">
                                {{ __('Your account is now protected with Two-Factor Authentication. You will be prompted for a secure PIN from your authenticator app during every login.') }}
                            </div>
                        </div>
                    </div>

                    <!-- Recovery Codes Section -->
                    <div class="space-y-4">
                        <div class="flex items-center space-x-2 text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">
                            <i data-lucide="key" class="w-3 h-3"></i>
                            <span>Recovery Management</span>
                        </div>
                        <livewire:pages::settings.two-factor.recovery-codes :$requiresConfirmation />
                    </div>

                    <!-- Danger Zone -->
                    <div class="pt-8 border-t border-slate-200/60 dark:border-darkmode-400">
                        <div class="text-xs font-bold text-danger uppercase tracking-widest mb-4 px-1">{{ __('Danger Zone') }}</div>
                        <flux:button
                            variant="danger"
                            wire:click="disable"
                            class="px-6 flex items-center shadow-lg shadow-danger/10"
                        >
                            <i data-lucide="shield-off" class="w-4 h-4 mr-2"></i>
                            {{ __('Disable Two-Factor Authentication') }}
                        </flux:button>
                    </div>
                </div>
            @else
                <!-- Disabled State -->
                <div class="space-y-8">
                    <div class="p-5 rounded-2xl border border-slate-200 dark:border-darkmode-400 bg-slate-50/50 dark:bg-darkmode-400/20 flex items-start space-x-5 transition-all hover:border-primary/30">
                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-darkmode-400 flex items-center justify-center text-slate-400 flex-shrink-0">
                            <i data-lucide="shield-alert" class="w-6 h-6"></i>
                        </div>
                        <div class="flex-1 pt-0.5">
                            <div class="flex items-center justify-between">
                                <div class="text-base font-bold text-slate-600 dark:text-slate-400">{{ __('2FA is Disabled') }}</div>
                                <div class="px-2 py-0.5 bg-slate-200 dark:bg-darkmode-400 text-slate-500 text-[10px] font-bold uppercase tracking-widest rounded tracking-widest">Recommended</div>
                            </div>
                            <div class="text-sm text-slate-500 mt-2 leading-relaxed">
                                {{ __('Enable Two-Factor Authentication to significantly increase your account security. Use any TOTP-supported app like Google Authenticator or Authy.') }}
                            </div>
                        </div>
                    </div>

                    <div class="pt-4">
                        <flux:modal.trigger name="two-factor-setup-modal">
                            <flux:button
                                variant="primary"
                                class="px-8 shadow-lg shadow-primary/20"
                                wire:click="$dispatch('start-two-factor-setup')"
                            >
                                <i data-lucide="shield-plus" class="w-4 h-4 mr-2"></i>
                                {{ __('Enable 2FA Now') }}
                            </flux:button>
                        </flux:modal.trigger>
                    </div>

                    <livewire:pages::settings.two-factor-setup-modal :requires-confirmation="$requiresConfirmation" />
                </div>
            @endif
        </div>
    </x-pages::settings.layout>
</section>
