<div>
    <div class="flex flex-col items-center mt-8 intro-y sm:flex-row">
        <h2 class="mr-auto text-lg font-medium">Platform Users</h2>
        <div class="flex w-full mt-4 sm:w-auto sm:mt-0">
            <button class="mr-2 border-primary transition duration-200 border shadow-sm inline-flex items-center justify-center px-3 font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary rounded-[0.6rem] py-2 px-4">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add New User
            </button>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 intro-y">
            <div class="box box--stacked p-0 overflow-hidden">
                <livewire:admin.components.users-table :limit="10" :paginated="true" :show-search="true" />
            </div>
        </div>
    </div>
</div>
