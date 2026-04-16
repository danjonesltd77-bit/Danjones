<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
    <style>
        /* Define the theme colors in case the CSS hasn't been recompiled yet */
        :root {
            --color-theme-1: 3 4 94;
            --color-theme-2: 12 74 110;
            --color-primary: 3 4 94;
        }

        /* Explicit gradient bypass to prevent Tailwind v4 variable parsing bugs on pseudo elements */
        .auth-brand-bg::before {
            background-image: linear-gradient(to bottom, #03045e, #0c4a6e) !important;
        }
    </style>
</head>
<body class="bg-slate-100 antialiased dark:bg-darkmode-600">
    <div class="container grid grid-cols-12 px-5 py-10 sm:px-10 sm:py-14 md:px-36 lg:h-screen lg:max-w-[1550px] lg:py-0 lg:pl-14 lg:pr-12 xl:px-24 2xl:max-w-[1750px]">
        <div class="relative z-50 h-full col-span-12 p-7 sm:p-14 bg-white rounded-2xl lg:bg-transparent lg:pr-10 lg:col-span-5 xl:pr-24 2xl:col-span-4 lg:p-0 before:content-[''] before:absolute before:inset-0 before:-mb-3.5 before:bg-white/40 before:rounded-2xl before:mx-5">
            <div class="relative z-10 flex flex-col justify-center w-full h-full py-2 lg:py-32">
                <div class="flex items-center">
                    <x-app-logo-icon class="size-12" />
                </div>
                <div class="mt-10">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

    <!-- Background Layer -->
    <div class="container fixed inset-0 grid h-screen w-screen grid-cols-12 pl-14 pr-12 lg:max-w-[1550px] xl:px-24 2xl:max-w-[1750px] pointer-events-none">
        <div class="relative h-screen col-span-12 lg:col-span-5 2xl:col-span-4 z-20 after:bg-white after:hidden after:lg:block after:content-[''] after:absolute after:right-0 after:inset-y-0 after:bg-gradient-to-b after:from-white after:to-slate-100/80 after:w-[800%] after:rounded-[0_1.2rem_1.2rem_0/0_1.7rem_1.7rem_0] before:content-[''] before:hidden before:lg:block before:absolute before:right-0 before:inset-y-0 before:my-6 before:bg-gradient-to-b before:from-white/10 before:to-slate-50/10 before:bg-white/50 before:w-[800%] before:-mr-4 before:rounded-[0_1.2rem_1.2rem_0/0_1.7rem_1.7rem_0]"></div>
        <div class="auth-brand-bg h-full col-span-7 2xl:col-span-8 lg:relative before:content-[''] before:absolute before:lg:-ml-10 before:left-0 before:inset-y-0 before:w-screen before:lg:w-[800%]">
            <div class="sticky top-0 z-10 flex-col justify-center hidden h-screen ml-16 lg:flex xl:ml-28 2xl:ml-36 pointer-events-auto">
                <div class="text-[2.6rem] font-medium leading-[1.4] text-white xl:text-5xl xl:leading-[1.2]">
                    Embrace Excellence <br> in Crypto Management
                </div>
                <div class="mt-5 text-base leading-relaxed text-white/70 xl:text-lg">
                    Experience the future of digital asset administration. Our platform offers professional grade tools, meticulous structure, and visually stunning interfaces to scale your crypto operations.
                </div>
                <div class="flex flex-col gap-3 mt-10 xl:flex-row xl:items-center">
                    <div class="flex items-center">
                        @foreach(range(1, 4) as $i)
                            <div class="{{ $loop->first ? '' : '-ml-3' }} h-9 w-9 2xl:h-11 2xl:w-11">
                                <img src="https://i.pravatar.cc/100?img={{ $i + 10 }}" alt="User avatar" class="rounded-full border-[3px] border-white/50">
                            </div>
                        @endforeach
                    </div>
                    <div class="text-base text-white/70 xl:ml-2 2xl:ml-3">
                        Join over <span class="font-bold text-white">7,000+</span> professionals growing their portfolios safely.
                    </div>
                </div>
            </div>
        </div>
    </div>

    @fluxScripts
</body>
</html>
