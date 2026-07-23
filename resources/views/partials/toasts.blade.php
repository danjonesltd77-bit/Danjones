<div x-data="{
    toasts: [],
    add(toast) {
        toast.id = Date.now()
        this.toasts.push(toast)
        setTimeout(() => this.remove(toast.id), 4500)
    },
    remove(id) {
        this.toasts = this.toasts.filter(t => t.id !== id)
    }
}" @toast.window="add($event.detail); console.log('Toast Event Received:', $event.detail)"
    x-init="@if(session()->has('toast'))
    add({{ json_encode(session('toast')) }})
    @endif"
    class="fixed top-0 right-0 z-[100] w-full max-w-sm p-4 space-y-4 pointer-events-none sm:p-6 mt-16">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="true" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex w-full max-w-sm overflow-hidden bg-white rounded-lg shadow-lg pointer-events-auto dark:bg-zinc-900 ring-1 ring-black/5 dark:ring-white/10">
            <div class="p-4 w-full">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <template x-if="toast.type === 'success'">
                            <i data-lucide="check-circle" class="w-5 h-5 text-success"></i>
                        </template>
                        <template x-if="toast.type === 'error' || toast.type === 'danger'">
                            <i data-lucide="alert-circle" class="w-5 h-5 text-danger"></i>
                        </template>
                        <template x-if="toast.type === 'info'">
                            <i data-lucide="info" class="w-5 h-5 text-primary"></i>
                        </template>
                        <template x-if="toast.type === 'warning'">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-warning"></i>
                        </template>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100" x-text="toast.message"></p>
                    </div>
                    <div class="flex-shrink-0 flex ml-4">
                        <button @click="remove(toast.id)"
                            class="inline-flex rounded-md text-slate-400 hover:text-slate-500 focus:outline-none">
                            <span class="sr-only">Close</span>
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        // Re-calculate lucide icons when toasts are added
        const observer = new MutationObserver((mutations) => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        const target = document.querySelector('[x-data=\"{ toasts: [], ... }\"]');
        if (target) {
            observer.observe(target, {
                childList: true,
                subtree: true
            });
        }
    });
</script>
