<div>
    <div class="flex flex-col items-center mt-8 intro-y sm:flex-row">
        <h2 class="mr-auto text-lg font-medium">Platform Transactions</h2>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 intro-y">
            <div class="box box--stacked p-0 overflow-hidden">
                <livewire:admin.components.transactions-table :limit="15" :paginated="true" :show-search="true" />
            </div>
        </div>
    </div>
</div>
