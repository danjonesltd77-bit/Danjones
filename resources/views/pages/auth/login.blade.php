<x-layouts::auth :title="__('Log in')">
    <div class="text-2xl font-medium">{{ __('Sign In') }}</div>
    <div class="mt-2.5 text-slate-600">
        {{ __('Don\'t have an account?') }}
        <a class="font-medium text-primary" href="{{ route('register') }}" wire:navigate>
            {{ __('Sign Up') }}
        </a>
    </div>

    <!-- Alert for Session Status or Errors -->
    @if (session('status') || $errors->any())
        <div role="alert"
            class="alert relative border rounded-[0.6rem] my-7 flex items-center border-primary/20 bg-primary/5 px-4 py-3 leading-[1.7] text-primary">
            <div class="mr-2">
                <i data-lucide="lightbulb" class="h-6 w-6 fill-primary/10 stroke-[0.8]"></i>
            </div>
            <div class="ml-1 mr-8 text-sm">
                @if (session('status'))
                    {{ session('status') }}
                @else
                    {{ __('Please check your credentials and try again.') }}
                @endif
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 flex flex-col gap-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label class="inline-block mb-2 text-sm font-medium text-slate-700">
                {{ __('Email Address') }}*
            </label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                autocomplete="email" placeholder="email@example.com"
                class="block w-full px-4 py-3 text-sm transition duration-200 border rounded-[0.6rem] border-slate-300/80 bg-white/70 focus:ring-4 focus:ring-primary/20 focus:border-primary/50 outline-none" />
        </div>

        <!-- Password -->
        <div class="mt-2 text-left">
            <div class="flex items-center justify-between mb-2">
                <label class="inline-block text-sm font-medium text-slate-700">
                    {{ __('Password') }}*
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                        class="text-xs text-slate-500 hover:text-primary transition-colors" wire:navigate>
                        {{ __('Forgot Password?') }}
                    </a>
                @endif
            </div>
            <div x-data="{ show: false }" class="relative">
                <input id="password" name="password" :type="show ? 'text' : 'password'" required
                    autocomplete="current-password" placeholder="************"
                    class="block w-full px-4 py-3 text-sm transition duration-200 border rounded-[0.6rem] border-slate-300/80 bg-white/70 focus:ring-4 focus:ring-primary/20 focus:border-primary/50 outline-none pr-12" />
                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-slate-600 transition-colors">
                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" style="display: none;">
                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                        <path d="M6.61 6.61A13.52 13.52 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                        <line x1="2" y1="2" x2="22" y2="22" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center mt-2">
            <input id="remember_me" name="remember" type="checkbox"
                class="h-4 w-4 text-primary transition duration-200 border-slate-300/80 rounded focus:ring-primary/20"
                {{ old('remember') ? 'checked' : '' }}>
            <label for="remember_me" class="ml-2 block text-sm text-slate-500 cursor-pointer select-none">
                {{ __('Remember me') }}
            </label>
        </div>

        <div class="mt-5 text-center xl:mt-8 xl:text-left">
            <button type="submit"
                class="transition duration-200 border shadow-sm inline-flex items-center justify-center px-3 font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary rounded-full w-full bg-gradient-to-r from-theme-1/70 to-theme-2/70 py-3.5 xl:mr-3">
                {{ __('Sign In') }}
            </button>

            @if (Route::has('register'))
                <a href="{{ route('register') }}"
                    class="transition duration-200 border shadow-sm inline-flex items-center justify-center px-3 font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-secondary/20 [&:hover:not(:disabled)]:dark:bg-darkmode-100/10 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-secondary text-slate-500 dark:border-darkmode-100/40 dark:text-slate-300 rounded-full mt-3 w-full bg-white/70 py-3.5"
                    wire:navigate>
                    {{ __('Sign Up') }}
                </a>
            @endif
        </div>
    </form>
</x-layouts::auth>
