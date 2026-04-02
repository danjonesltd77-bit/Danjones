<x-layouts::auth :title="__('Forgot password')">
    <div class="text-2xl font-medium">{{ __('Forgot Password') }}</div>
    <div class="mt-2.5 text-slate-600">
        {{ __('Enter your email to receive a password reset link and regain access to your account.') }}
    </div>

    <!-- Alert for Session Status or Errors -->
    @if (session('status') || $errors->any())
        <div role="alert"
            class="alert relative border rounded-[0.6rem] my-7 flex items-center border-primary/20 bg-primary/5 px-4 py-3 leading-[1.7] text-primary">
            <div class="mr-2">
                <i data-lucide="mail" class="h-6 w-6 fill-primary/10 stroke-[0.8]"></i>
            </div>
            <div class="ml-1 mr-8 text-sm">
                @if (session('status'))
                    {{ session('status') }}
                @else
                    {{ __('Please check your email address and try again.') }}
                @endif
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 flex flex-col gap-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label class="inline-block mb-2 text-sm font-medium text-slate-700">
                {{ __('Email Address') }}*
            </label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                placeholder="email@example.com"
                class="block w-full px-4 py-3 text-sm transition duration-200 border rounded-[0.6rem] border-slate-300/80 bg-white/70 focus:ring-4 focus:ring-primary/20 focus:border-primary/50 outline-none" />
        </div>

        <div class="mt-5 text-center xl:mt-8 xl:text-left">
            <button type="submit"
                class="transition duration-200 border shadow-sm inline-flex items-center justify-center px-3 font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary rounded-full w-full bg-gradient-to-r from-theme-1/70 to-theme-2/70 py-3.5 xl:mr-3">
                {{ __('Email Password Reset Link') }}
            </button>

            <a href="{{ route('login') }}"
                class="transition duration-200 border shadow-sm inline-flex items-center justify-center px-3 font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-secondary/20 [&:hover:not(:disabled)]:dark:bg-darkmode-100/10 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-secondary text-slate-500 dark:border-darkmode-100/40 dark:text-slate-300 rounded-full mt-3 w-full bg-white/70 py-3.5"
                wire:navigate>
                {{ __('Return to Login') }}
            </a>
        </div>
    </form>
</x-layouts::auth>
