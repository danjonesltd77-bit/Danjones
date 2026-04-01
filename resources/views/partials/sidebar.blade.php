<div
    class="side-menu__content h-full box bg-white/[0.95] rounded-none xl:rounded-xl z-20 relative w-[275px] duration-300 transition-[width] group-[.side-menu--collapsed]:xl:w-[91px] group-[.side-menu--collapsed.side-menu--on-hover]:xl:shadow-[6px_0_12px_-4px_#0000000f] group-[.side-menu--collapsed.side-menu--on-hover]:xl:w-[275px] overflow-hidden flex flex-col">
    <div
        class="flex-none hidden xl:flex items-center z-10 px-5 h-[65px] w-[275px] overflow-hidden relative duration-300 group-[.side-menu--collapsed]:xl:w-[91px] group-[.side-menu--collapsed.side-menu--on-hover]:xl:w-[275px]">
        <a class="flex items-center transition-[margin] duration-300 group-[.side-menu--collapsed.side-menu--on-hover]:xl:ml-0 group-[.side-menu--collapsed]:xl:ml-2"
            href="{{ route('dashboard') }}">
            <div
                class="flex h-[34px] w-[34px] items-center justify-center rounded-lg bg-gradient-to-b from-theme-1 to-theme-2/80 transition-transform ease-in-out group-[.side-menu--collapsed.side-menu--on-hover]:xl:-rotate-180">
                <div class="relative h-[16px] w-[16px] -rotate-45 [&_div]:bg-white">
                    <div class="absolute inset-y-0 left-0 my-auto h-[75%] w-[21%] rounded-full opacity-50">
                    </div>
                    <div class="absolute inset-0 m-auto h-[120%] w-[21%] rounded-full"></div>
                    <div class="absolute inset-y-0 right-0 my-auto h-[75%] w-[21%] rounded-full opacity-50">
                    </div>
                </div>
            </div>
            <div
                class="ml-3.5 font-medium transition-opacity group-[.side-menu--collapsed.side-menu--on-hover]:xl:opacity-100 group-[.side-menu--collapsed]:xl:opacity-0">
                {{ env('APP_NAME') }}
            </div>
        </a>
        <a class="toggle-compact-menu ml-auto hidden h-[20px] w-[20px] items-center justify-center rounded-full border border-slate-600/40 transition-[opacity,transform] hover:bg-slate-600/5 group-[.side-menu--collapsed]:xl:rotate-180 group-[.side-menu--collapsed.side-menu--on-hover]:xl:opacity-100 group-[.side-menu--collapsed]:xl:opacity-0 xl:flex"
            href="">
            <i data-tw-merge="" data-lucide="arrow-left" class="h-3.5 w-3.5 stroke-[1.3]"></i>
        </a>
    </div>
    <div
        class="scrollable-ref w-full h-full z-20 px-5 overflow-y-auto overflow-x-hidden pb-3 [-webkit-mask-image:-webkit-linear-gradient(top,rgba(0,0,0,0),black_30px)] [&:-webkit-scrollbar]:w-0 [&:-webkit-scrollbar]:bg-transparent [&_.simplebar-content]:p-0 [&_.simplebar-track.simplebar-vertical]:w-[10px] [&_.simplebar-track.simplebar-vertical]:mr-0.5 [&_.simplebar-track.simplebar-vertical_.simplebar-scrollbar]:before:bg-slate-400/30">
        <ul class="scrollable">
            <!-- BEGIN: First Child -->
            @can('manage dashboard')
                <li class="side-menu__divider">
                    DASHBOARD
                </li>
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="side-menu__link {{ request()->routeIs('dashboard') ? 'side-menu__link--active' : '' }}">
                        <i data-tw-merge="" data-lucide="gauge-circle" class="stroke-[1] w-5 h-5 side-menu__link__icon"></i>
                        <div class="side-menu__link__title">Overview</div>
                    </a>
                </li>
            @endcan

            @can('manage wallets')
                <li class="side-menu__divider">
                    ASSETS
                </li>
                <li>
                    <a href="#" class="side-menu__link">
                        <i data-tw-merge="" data-lucide="wallet" class="stroke-[1] w-5 h-5 side-menu__link__icon"></i>
                        <div class="side-menu__link__title">Wallets</div>
                    </a>
                </li>
                <li>
                    <a href="#" class="side-menu__link">
                        <i data-tw-merge="" data-lucide="arrow-right-left"
                            class="stroke-[1] w-5 h-5 side-menu__link__icon"></i>
                        <div class="side-menu__link__title">Transactions</div>
                    </a>
                </li>
            @endcan

            @can('manage p2p')
                <li class="side-menu__divider">
                    P2P TRADING
                </li>
                <li>
                    <a href="#" class="side-menu__link">
                        <i data-tw-merge="" data-lucide="megaphone" class="stroke-[1] w-5 h-5 side-menu__link__icon"></i>
                        <div class="side-menu__link__title">Advertisements</div>
                    </a>
                </li>
                <li>
                    <a href="#" class="side-menu__link">
                        <i data-tw-merge="" data-lucide="shopping-cart"
                            class="stroke-[1] w-5 h-5 side-menu__link__icon"></i>
                        <div class="side-menu__link__title">Trades</div>
                    </a>
                </li>
            @endcan

            @canany(['manage roles', 'manage kyc'])
                <li class="side-menu__divider">
                    USER MANAGEMENT
                </li>
            @endcanany

            @can('manage roles')
                <li>
                    <a href="#" class="side-menu__link">
                        <i data-tw-merge="" data-lucide="users" class="stroke-[1] w-5 h-5 side-menu__link__icon"></i>
                        <div class="side-menu__link__title">User List</div>
                    </a>
                </li>
            @endcan

            @can('manage kyc')
                <li>
                    <a href="#" class="side-menu__link">
                        <i data-tw-merge="" data-lucide="shield-check" class="stroke-[1] w-5 h-5 side-menu__link__icon"></i>
                        <div class="side-menu__link__title">KYC Verifications</div>
                    </a>
                </li>
            @endcan

            @can('manage roles')
                <li>
                    <a href="{{ route('admin.roles.index') }}"
                        class="side-menu__link {{ request()->routeIs('admin.roles.index') ? 'side-menu__link--active' : '' }}">
                        <i data-tw-merge="" data-lucide="key" class="stroke-[1] w-5 h-5 side-menu__link__icon"></i>
                        <div class="side-menu__link__title">Roles & Permissions</div>
                    </a>
                </li>
            @endcan

            <li class="side-menu__divider">
                SETTINGS
            </li>
            <li>
                <a href="{{ route('profile.edit') }}"
                    class="side-menu__link {{ request()->routeIs('profile.edit') ? 'side-menu__link--active' : '' }}">
                    <i data-tw-merge="" data-lucide="user" class="stroke-[1] w-5 h-5 side-menu__link__icon"></i>
                    <div class="side-menu__link__title">Profile Info</div>
                </a>
            </li>
            <li>
                <a href="{{ route('user-password.edit') }}"
                    class="side-menu__link {{ request()->routeIs('user-password.edit') ? 'side-menu__link--active' : '' }}">
                    <i data-tw-merge="" data-lucide="lock" class="stroke-[1] w-5 h-5 side-menu__link__icon"></i>
                    <div class="side-menu__link__title">Security</div>
                </a>
            </li>
            <li>
                <a href="{{ route('two-factor.show') }}"
                    class="side-menu__link {{ request()->routeIs('two-factor.show') ? 'side-menu__link--active' : '' }}">
                    <i data-tw-merge="" data-lucide="fingerprint" class="stroke-[1] w-5 h-5 side-menu__link__icon"></i>
                    <div class="side-menu__link__title">Two-factor Auth</div>
                </a>
            </li>
            <!-- END: First Child -->
        </ul>
    </div>
</div>
