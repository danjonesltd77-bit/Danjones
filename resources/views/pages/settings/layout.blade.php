@include('partials.settings-heading')

<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <!-- Sidebar Navigation -->
    <div class="col-span-12 md:col-span-4 lg:col-span-3">
        <div class="box p-3 h-full">
            <nav class="flex flex-col gap-y-1">
                <a href="{{ route('profile.edit') }}" wire:navigate 
                    class="flex items-center px-4 py-3 rounded-lg transition-all group {{ request()->routeIs('profile.edit') ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-darkmode-400/50 hover:text-primary' }}">
                    <i data-lucide="user" class="w-4 h-4 mr-3 stroke-[1.5] {{ request()->routeIs('profile.edit') ? '' : 'group-hover:text-primary' }}"></i>
                    <span class="text-xs font-medium uppercase tracking-wider">{{ __('Profile') }}</span>
                </a>
                
                <a href="{{ route('user-password.edit') }}" wire:navigate 
                    class="flex items-center px-4 py-3 rounded-lg transition-all group {{ request()->routeIs('user-password.edit') ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-darkmode-400/50 hover:text-primary' }}">
                    <i data-lucide="shield-check" class="w-4 h-4 mr-3 stroke-[1.5] {{ request()->routeIs('user-password.edit') ? '' : 'group-hover:text-primary' }}"></i>
                    <span class="text-xs font-medium uppercase tracking-wider">{{ __('Security') }}</span>
                </a>

                @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                    <a href="{{ route('two-factor.show') }}" wire:navigate 
                        class="flex items-center px-4 py-3 rounded-lg transition-all group {{ request()->routeIs('two-factor.show') ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-darkmode-400/50 hover:text-primary' }}">
                        <i data-lucide="key" class="w-4 h-4 mr-3 stroke-[1.5] {{ request()->routeIs('two-factor.show') ? '' : 'group-hover:text-primary' }}"></i>
                        <span class="text-xs font-medium uppercase tracking-wider">{{ __('Two-Factor') }}</span>
                    </a>
                @endif

                <a href="{{ route('appearance.edit') }}" wire:navigate 
                    class="flex items-center px-4 py-3 rounded-lg transition-all group {{ request()->routeIs('appearance.edit') ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-darkmode-400/50 hover:text-primary' }}">
                    <i data-lucide="palette" class="w-4 h-4 mr-3 stroke-[1.5] {{ request()->routeIs('appearance.edit') ? '' : 'group-hover:text-primary' }}"></i>
                    <span class="text-xs font-medium uppercase tracking-wider">{{ __('Appearance') }}</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="col-span-12 md:col-span-8 lg:col-span-9">
        <div class="box box--stacked p-8 min-h-[400px]">
            <div class="max-w-2xl">
                <div class="mb-8">
                    <div class="text-lg font-medium text-slate-700 dark:text-slate-300">{{ $heading ?? '' }}</div>
                    <div class="text-sm text-slate-500 mt-1">{{ $subheading ?? '' }}</div>
                </div>

                <div>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
