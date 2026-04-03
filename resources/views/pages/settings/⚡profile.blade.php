<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Identity & Profile')" :subheading="__('Update your personal details and account email')">
        <form wire:submit="updateProfileInformation" class="mt-8 w-full space-y-8">
            <!-- Name Field -->
            <div class="space-y-2">
                <div class="flex items-center space-x-2 text-xs font-bold text-slate-400 uppercase tracking-widest">
                    <i data-lucide="user" class="w-3 h-3"></i>
                    <span>Full Name</span>
                </div>
                <flux:input wire:model="name" type="text" required autofocus autocomplete="name" class="!bg-slate-50/50 dark:!bg-darkmode-400/30" />
            </div>

            <!-- Email Field -->
            <div class="space-y-4">
                <div class="space-y-2">
                    <div class="flex items-center space-x-2 text-xs font-bold text-slate-400 uppercase tracking-widest">
                        <i data-lucide="mail" class="w-3 h-3"></i>
                        <span>Email Address</span>
                    </div>
                    <flux:input wire:model="email" type="email" required autocomplete="email" class="!bg-slate-50/50 dark:!bg-darkmode-400/30" />
                </div>

                @if ($this->hasUnverifiedEmail)
                    <div class="p-4 rounded-xl border border-warning/20 bg-warning/5 flex items-start space-x-4 animate-in fade-in slide-in-from-top-4 duration-500">
                        <div class="w-10 h-10 rounded-full bg-warning/10 flex items-center justify-center text-warning flex-shrink-0">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('Email Verification Required') }}</div>
                            <div class="text-xs text-slate-500 mt-1 leading-relaxed">
                                {{ __('Your email address is currently unverified. Please verify your address to ensure full access to all features.') }}
                            </div>
                            <div class="mt-3">
                                <flux:button variant="ghost" size="sm" class="!px-0 !py-0 !text-warning hover:!underline font-bold text-xs" wire:click.prevent="resendVerificationNotification">
                                    {{ __('Resend Verification Email') }}
                                </flux:button>
                            </div>
                        </div>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <div class="p-4 rounded-xl border border-success/20 bg-success/5 flex items-center space-x-3">
                            <i data-lucide="check-circle" class="w-4 h-4 text-success"></i>
                            <div class="text-[11px] font-bold text-success uppercase tracking-wider">
                                {{ __('A new verification link has been sent.') }}
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4">
                <div class="flex items-center gap-4">
                    <flux:button variant="primary" type="submit" class="px-8 shadow-lg shadow-primary/20">
                        {{ __('Save Changes') }}
                    </flux:button>

                    <div x-data="{ show: false }" 
                         x-on:profile-updated.window="show = true; setTimeout(() => show = false, 2000)"
                         x-show="show"
                         x-transition.out.opacity.duration.1500ms
                         style="display: none;"
                         class="flex items-center text-success space-x-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span class="text-xs font-bold uppercase tracking-widest">{{ __('Profile Saved') }}</span>
                    </div>
                </div>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <div class="mt-12 pt-12 border-t border-slate-200/60 dark:border-darkmode-400">
                <div class="text-xs font-bold text-danger uppercase tracking-widest mb-6 px-1">{{ __('Danger Zone') }}</div>
                <livewire:pages::settings.delete-user-form />
            </div>
        @endif
    </x-pages::settings.layout>
</section>
