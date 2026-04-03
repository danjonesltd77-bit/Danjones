<?php

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Password settings')] class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    <flux:heading class="sr-only">{{ __('Password settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Security & Access')" :subheading="__('Keep your account protected with a strong, rotating password')">
        <form method="POST" wire:submit="updatePassword" class="mt-8 w-full space-y-8">
            <!-- Current Password -->
            <div class="space-y-2">
                <div class="flex items-center space-x-2 text-xs font-bold text-slate-400 uppercase tracking-widest">
                    <i data-lucide="lock" class="w-3 h-3"></i>
                    <span>Current Password</span>
                </div>
                <flux:input wire:model="current_password" type="password" required autocomplete="current-password" class="!bg-slate-50/50 dark:!bg-darkmode-400/30" />
            </div>

            <!-- New Password -->
            <div class="space-y-2">
                <div class="flex items-center space-x-2 text-xs font-bold text-slate-400 uppercase tracking-widest">
                    <i data-lucide="key" class="w-3 h-3"></i>
                    <span>New Password</span>
                </div>
                <flux:input wire:model="password" type="password" required autocomplete="new-password" class="!bg-slate-50/50 dark:!bg-darkmode-400/30" />
            </div>

            <!-- Confirm Password -->
            <div class="space-y-2">
                <div class="flex items-center space-x-2 text-xs font-bold text-slate-400 uppercase tracking-widest">
                    <i data-lucide="shield-check" class="w-3 h-3"></i>
                    <span>Confirm New Password</span>
                </div>
                <flux:input wire:model="password_confirmation" type="password" required autocomplete="new-password" class="!bg-slate-50/50 dark:!bg-darkmode-400/30" />
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4">
                <div class="flex items-center gap-4">
                    <flux:button variant="primary" type="submit" class="px-8 shadow-lg shadow-primary/20">
                        {{ __('Update Password') }}
                    </flux:button>

                    <div x-data="{ show: false }" 
                         x-on:password-updated.window="show = true; setTimeout(() => show = false, 2000)"
                         x-show="show"
                         x-transition.out.opacity.duration.1500ms
                         style="display: none;"
                         class="flex items-center text-success space-x-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span class="text-xs font-bold uppercase tracking-widest">{{ __('Security Updated') }}</span>
                    </div>
                </div>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
