<div>
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">

        <!-- Top Row Metrics -->
        <div class="col-span-12">
            <div class="flex h-10 items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">Platform Overview</div>
            </div>

            <div class="mt-3.5 grid grid-cols-12 gap-5">
                <!-- Total Users -->
                <div class="box box--stacked col-span-12 flex flex-col p-5 sm:col-span-6 xl:col-span-3">
                    <div class="flex items-center">
                        <div
                            class="h-[54px] w-[54px] flex items-center justify-center rounded-full border border-primary/80 bg-slate-50 text-primary">
                            <i data-lucide="users" class="w-6 h-6"></i>
                        </div>
                        <div class="ml-4">
                            <div class="-mt-0.5 text-lg font-medium text-slate-700 dark:text-slate-300">Total Users
                            </div>
                        </div>
                    </div>
                    <div
                        class="box mt-10 rounded-[0.6rem] border border-dashed border-slate-300/80 px-4 py-2.5 shadow-sm">
                        <div class="flex items-center">
                            <div class="text-2xl font-medium leading-tight text-slate-800 dark:text-slate-300">
                                {{ number_format($this->totalUsers) }}</div>
                        </div>
                        <div class="mt-1 text-sm text-slate-500">Registered Accounts</div>
                    </div>
                </div>

                <!-- Total Transactions -->
                <div class="box box--stacked col-span-12 flex flex-col p-5 sm:col-span-6 xl:col-span-3">
                    <div class="flex items-center">
                        <div
                            class="h-[54px] w-[54px] flex items-center justify-center rounded-full border border-primary/80 bg-slate-50 text-primary">
                            <i data-lucide="arrow-right-left" class="w-6 h-6"></i>
                        </div>
                        <div class="ml-4">
                            <div class="-mt-0.5 text-lg font-medium text-slate-700 dark:text-slate-300">Transactions
                            </div>
                        </div>
                    </div>
                    <div
                        class="box mt-10 rounded-[0.6rem] border border-dashed border-slate-300/80 px-4 py-2.5 shadow-sm">
                        <div class="flex items-center">
                            <div class="text-2xl font-medium leading-tight text-slate-800 dark:text-slate-300">
                                {{ number_format($this->totalTransactions) }}</div>
                        </div>

                        <div class="mt-1 text-sm text-slate-500">Platform Payments</div>
                    </div>
                </div>

                <!-- P2P Trades -->
                <div class="box box--stacked col-span-12 flex flex-col p-5 sm:col-span-6 xl:col-span-3">
                    <div class="flex items-center">
                        <div
                            class="h-[54px] w-[54px] flex items-center justify-center rounded-full border border-primary/80 bg-slate-50 text-primary">
                            <i data-lucide="shopping-cart" class="w-6 h-6"></i>
                        </div>
                        <div class="ml-4">
                            <div class="-mt-0.5 text-lg font-medium text-slate-700 dark:text-slate-300">P2P Trades</div>
                        </div>
                    </div>
                    <div
                        class="box mt-10 rounded-[0.6rem] border border-dashed border-slate-300/80 px-4 py-2.5 shadow-sm">
                        <div class="flex items-center">
                            <div class="text-2xl font-medium leading-tight text-slate-800 dark:text-slate-300">
                                {{ number_format($this->totalP2PTrades) }}</div>
                        </div>
                        <div class="mt-1 text-sm text-slate-500">Completed Orders</div>
                    </div>
                </div>

                <!-- P2P Ads -->
                <div class="box box--stacked col-span-12 flex flex-col p-5 sm:col-span-6 xl:col-span-3">
                    <div class="flex items-center">
                        <div
                            class="h-[54px] w-[54px] flex items-center justify-center rounded-full border border-primary/80 bg-slate-50 text-primary">
                            <i data-lucide="megaphone" class="w-6 h-6"></i>
                        </div>
                        <div class="ml-4">
                            <div class="-mt-0.5 text-lg font-medium text-slate-700 dark:text-slate-300">P2P Ads</div>
                        </div>
                    </div>
                    <div
                        class="box mt-10 rounded-[0.6rem] border border-dashed border-slate-300/80 px-4 py-2.5 shadow-sm">
                        <div class="flex items-center">
                            <div class="text-2xl font-medium leading-tight text-slate-800 dark:text-slate-300">
                                {{ number_format($this->totalP2PAds) }}</div>
                        </div>
                        <div class="mt-1 text-sm text-slate-500">Active Advertisements</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Data Lists -->
        <div class="col-span-12 grid grid-cols-12 gap-x-6 gap-y-10">
            <!-- Latest Users -->
            <div class="col-span-12 xl:col-span-6 flex flex-col gap-y-4">
                <div class="flex h-10 items-center justify-between">
                    <div class="text-base font-medium ">Recent Users</div>
                    <a href="{{ route('admin.users.index') }}" class="text-primary hover:underline text-sm"
                        wire:navigate>View
                        All Users</a>
                </div>
                <div class="box box--stacked p-0">
                    <livewire:admin.components.users-table :limit="5" />
                </div>
            </div>

            <!-- Latest Transactions -->
            <div class="col-span-12 xl:col-span-6 flex flex-col gap-y-4">
                <div class="flex h-10 items-center justify-between">
                    <div class="text-base font-medium">Recent Transactions</div>
                    <a href="{{ route('admin.transactions.index') }}" class="text-primary hover:underline text-sm"
                        wire:navigate>View
                        All Transactions</a>
                </div>
                <div class="box box--stacked p-0">
                    <livewire:admin.components.transactions-table :limit="5" />
                </div>
            </div>
        </div>

    </div>
</div>
