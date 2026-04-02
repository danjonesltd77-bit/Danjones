<x-layouts::auth :title="__('Email verification')">
    <div class="text-2xl font-medium">{{ __('Verify Email') }}</div>
    <div class="mt-2.5 text-slate-600">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?') }}
    </div>

    <!-- Alert for Session Status -->
    @if (session('status') == 'verification-link-sent')
        <div role="alert" class="alert relative border rounded-[0.6rem] my-7 flex items-center border-primary/20 bg-primary/5 px-4 py-3 leading-[1.7] text-primary">
            <div class="mr-2">
                <i data-lucide="mail-check" class="h-6 w-6 fill-primary/10 stroke-[0.8]"></i>
            </div>
            <div class="ml-1 mr-8 text-sm">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        </div>
    @endif

    <div class="mt-6 flex flex-col gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="transition duration-200 border shadow-sm inline-flex items-center justify-center px-3 font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary rounded-full w-full bg-gradient-to-r from-theme-1/70 to-theme-2/70 py-3.5 xl:mr-3">
                {{ __('Resend Verification Email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="transition duration-200 border shadow-sm inline-flex items-center justify-center px-3 font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-secondary/20 [&:hover:not(:disabled)]:dark:bg-darkmode-100/10 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-secondary text-slate-500 dark:border-darkmode-100/40 dark:text-slate-300 rounded-full mt-3 w-full bg-white/70 py-3.5">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-layouts::auth>
