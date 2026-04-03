<div>
    <div class="flex flex-col items-center mt-8 intro-y sm:flex-row">
        <h2 class="text-base font-medium group-[.mode--light]:text-white">Platform Users</h2>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 intro-y">
            <div class="box box--stacked p-0 overflow-hidden">
                <livewire:admin.components.users-table :limit="10" :paginated="true" :show-search="true" />
            </div>
        </div>
    </div>
</div>
