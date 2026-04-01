<x-layouts::app>
     <div class="container">
     <div class="grid grid-cols-12 gap-x-6 gap-y-10">
         <div class="col-span-12">
             <div class="flex h-10 items-center">
                 <div class="text-base font-medium group-[.mode--light]:text-white">
                     Wallet
                 </div>
             </div>
             <div class="mt-3.5 grid grid-cols-12 gap-5">
                 <div class="box box--stacked col-span-12 flex flex-col p-5 sm:col-span-6 xl:col-span-3">
                     <div data-tw-merge="" data-tw-placement="bottom-end"
                         class="dropdown absolute right-0 top-0 mr-5 mt-5"><button data-tw-toggle="dropdown"
                             aria-expanded="false" class="cursor-pointer h-5 w-5 text-slate-500"><i data-tw-merge=""
                                 data-lucide="more-vertical"
                                 class="stroke-[1] h-6 w-6 fill-slate-400/70 stroke-slate-400/70"></i>
                         </button>
                         <div data-transition="" data-selector=".show"
                             data-enter="transition-all ease-linear duration-150"
                             data-enter-from="absolute !mt-5 invisible opacity-0 translate-y-1"
                             data-enter-to="!mt-1 visible opacity-100 translate-y-0"
                             data-leave="transition-all ease-linear duration-150"
                             data-leave-from="!mt-1 visible opacity-100 translate-y-0"
                             data-leave-to="absolute !mt-5 invisible opacity-0 translate-y-1"
                             class="dropdown-menu absolute z-[9999] hidden">
                             <div data-tw-merge=""
                                 class="dropdown-content rounded-md border-transparent bg-white p-2 shadow-[0px_3px_10px_#00000017] dark:border-transparent dark:bg-darkmode-600 w-40">
                                 <a
                                     class="cursor-pointer flex items-center p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-200/60 dark:bg-darkmode-600 dark:hover:bg-darkmode-400 dropdown-item"><i
                                         data-tw-merge="" data-lucide="copy" class="stroke-[1] mr-2 h-4 w-4"></i>
                                     Copy Link</a>
                                 <a
                                     class="cursor-pointer flex items-center p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-200/60 dark:bg-darkmode-600 dark:hover:bg-darkmode-400 dropdown-item"><i
                                         data-tw-merge="" data-lucide="trash" class="stroke-[1] mr-2 h-4 w-4"></i>
                                     Delete</a>
                             </div>
                         </div>
                     </div>
                     <div class="flex items-center">
                         <div
                             class="h-[54px] w-[54px] cursor-pointer rounded-full border border-primary/80 bg-slate-50 p-0.5">
                             <div class="h-full w-full rounded-full border border-slate-300/70 bg-white p-1">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" version="1.1"
                                     shape-rendering="geometricPrecision" text-rendering="geometricPrecision"
                                     image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd"
                                     viewBox="0 0 4091.27 4091.73">
                                     <g>
                                         <metadata />
                                         <g>
                                             <path fill="#F7931A" fill-rule="nonzero"
                                                 d="M4030.06 2540.77c-273.24,1096.01 -1383.32,1763.02 -2479.46,1489.71 -1095.68,-273.24 -1762.69,-1383.39 -1489.33,-2479.31 273.12,-1096.13 1383.2,-1763.19 2479,-1489.95 1096.06,273.24 1763.03,1383.51 1489.76,2479.57l0.02 -0.02z" />
                                             <path fill="white" fill-rule="nonzero"
                                                 d="M2947.77 1754.38c40.72,-272.26 -166.56,-418.61 -450,-516.24l91.95 -368.8 -224.5 -55.94 -89.51 359.09c-59.02,-14.72 -119.63,-28.59 -179.87,-42.34l90.16 -361.46 -224.36 -55.94 -92 368.68c-48.84,-11.12 -96.81,-22.11 -143.35,-33.69l0.26 -1.16 -309.59 -77.31 -59.72 239.78c0,0 166.56,38.18 163.05,40.53 90.91,22.69 107.35,82.87 104.62,130.57l-104.74 420.15c6.26,1.59 14.38,3.89 23.34,7.49 -7.49,-1.86 -15.46,-3.89 -23.73,-5.87l-146.81 588.57c-11.11,27.62 -39.31,69.07 -102.87,53.33 2.25,3.26 -163.17,-40.72 -163.17,-40.72l-111.46 256.98 292.15 72.83c54.35,13.63 107.61,27.89 160.06,41.3l-92.9 373.03 224.24 55.94 92 -369.07c61.26,16.63 120.71,31.97 178.91,46.43l-91.69 367.33 224.51 55.94 92.89 -372.33c382.82,72.45 670.67,43.24 791.83,-303.02 97.63,-278.78 -4.86,-439.58 -206.26,-544.44 146.69,-33.83 257.18,-130.31 286.64,-329.61l-0.07 -0.05zm-512.93 719.26c-69.38,278.78 -538.76,128.08 -690.94,90.29l123.28 -494.2c152.17,37.99 640.17,113.17 567.67,403.91zm69.43 -723.3c-63.29,253.58 -453.96,124.75 -580.69,93.16l111.77 -448.21c126.73,31.59 534.85,90.55 468.94,355.05l-0.02 0z" />
                                         </g>
                                     </g>
                                 </svg>
                             </div>
                         </div>
                         <div class="ml-4">
                             <div class="-mt-0.5 text-lg font-medium text-primary">
                                 Bitcoin
                             </div>
                             <div class="mt-0.5 text-slate-500">BTC/USDT</div>
                         </div>
                     </div>
                     <div
                         class="box mt-16 rounded-[0.6rem] border border-dashed border-slate-300/80 px-4 py-2.5 shadow-sm">
                         <div class="flex items-center">
                             <div class="text-xl font-medium leading-tight">23,46</div>
                             <div class="ml-2.5 flex items-center font-medium text-success">
                                 +2%
                                 <i data-tw-merge="" data-lucide="chevron-up" class="ml-px h-4 w-4 stroke-[1.5]"></i>
                             </div>
                         </div>
                         <div class="mt-1 text-base text-slate-500">$7,321,010,00</div>
                     </div>
                 </div>
                 <div class="box box--stacked col-span-12 flex flex-col p-5 sm:col-span-6 xl:col-span-3">
                     <div data-tw-merge="" data-tw-placement="bottom-end"
                         class="dropdown absolute right-0 top-0 mr-5 mt-5"><button data-tw-toggle="dropdown"
                             aria-expanded="false" class="cursor-pointer h-5 w-5 text-slate-500"><i data-tw-merge=""
                                 data-lucide="more-vertical"
                                 class="stroke-[1] h-6 w-6 fill-slate-400/70 stroke-slate-400/70"></i>
                         </button>
                         <div data-transition="" data-selector=".show"
                             data-enter="transition-all ease-linear duration-150"
                             data-enter-from="absolute !mt-5 invisible opacity-0 translate-y-1"
                             data-enter-to="!mt-1 visible opacity-100 translate-y-0"
                             data-leave="transition-all ease-linear duration-150"
                             data-leave-from="!mt-1 visible opacity-100 translate-y-0"
                             data-leave-to="absolute !mt-5 invisible opacity-0 translate-y-1"
                             class="dropdown-menu absolute z-[9999] hidden">
                             <div data-tw-merge=""
                                 class="dropdown-content rounded-md border-transparent bg-white p-2 shadow-[0px_3px_10px_#00000017] dark:border-transparent dark:bg-darkmode-600 w-40">
                                 <a
                                     class="cursor-pointer flex items-center p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-200/60 dark:bg-darkmode-600 dark:hover:bg-darkmode-400 dropdown-item"><i
                                         data-tw-merge="" data-lucide="copy" class="stroke-[1] mr-2 h-4 w-4"></i>
                                     Copy Link</a>
                                 <a
                                     class="cursor-pointer flex items-center p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-200/60 dark:bg-darkmode-600 dark:hover:bg-darkmode-400 dropdown-item"><i
                                         data-tw-merge="" data-lucide="trash" class="stroke-[1] mr-2 h-4 w-4"></i>
                                     Delete</a>
                             </div>
                         </div>
                     </div>
                     <div class="flex items-center">
                         <div
                             class="h-[54px] w-[54px] cursor-pointer rounded-full border border-primary/80 bg-slate-50 p-0.5">
                             <div class="h-full w-full rounded-full border border-slate-300/70 bg-white p-1">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" version="1.1"
                                     shape-rendering="geometricPrecision" text-rendering="geometricPrecision"
                                     image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd"
                                     viewBox="0 0 784.37 1277.39">
                                     <g>
                                         <metadata />
                                         <g>
                                             <g>
                                                 <polygon fill="#343434" fill-rule="nonzero"
                                                     points="392.07,0 383.5,29.11 383.5,873.74 392.07,882.29 784.13,650.54 " />
                                                 <polygon fill="#8C8C8C" fill-rule="nonzero"
                                                     points="392.07,0 -0,650.54 392.07,882.29 392.07,472.33 " />
                                                 <polygon fill="#3C3C3B" fill-rule="nonzero"
                                                     points="392.07,956.52 387.24,962.41 387.24,1263.28 392.07,1277.38 784.37,724.89 " />
                                                 <polygon fill="#8C8C8C" fill-rule="nonzero"
                                                     points="392.07,1277.38 392.07,956.52 -0,724.89 " />
                                                 <polygon fill="#141414" fill-rule="nonzero"
                                                     points="392.07,882.29 784.13,650.54 392.07,472.33 " />
                                                 <polygon fill="#393939" fill-rule="nonzero"
                                                     points="0,650.54 392.07,882.29 392.07,472.33 " />
                                             </g>
                                         </g>
                                     </g>
                                 </svg>
                             </div>
                         </div>
                         <div class="ml-4">
                             <div class="-mt-0.5 text-lg font-medium text-primary">
                                 Ethereum
                             </div>
                             <div class="mt-0.5 text-slate-500">ETH/USDT</div>
                         </div>
                     </div>
                     <div
                         class="box mt-16 rounded-[0.6rem] border border-dashed border-slate-300/80 px-4 py-2.5 shadow-sm">
                         <div class="flex items-center">
                             <div class="text-xl font-medium leading-tight">203,15</div>
                             <div class="ml-2.5 flex items-center font-medium text-danger">
                                 -3%
                                 <i data-tw-merge="" data-lucide="chevron-down"
                                     class="ml-px h-4 w-4 stroke-[1.5]"></i>
                             </div>
                         </div>
                         <div class="mt-1 text-base text-slate-500">$1,421,990,00</div>
                     </div>
                 </div>
                 <div class="box box--stacked col-span-12 flex flex-col p-5 sm:col-span-6 xl:col-span-3">
                     <div data-tw-merge="" data-tw-placement="bottom-end"
                         class="dropdown absolute right-0 top-0 mr-5 mt-5"><button data-tw-toggle="dropdown"
                             aria-expanded="false" class="cursor-pointer h-5 w-5 text-slate-500"><i data-tw-merge=""
                                 data-lucide="more-vertical"
                                 class="stroke-[1] h-6 w-6 fill-slate-400/70 stroke-slate-400/70"></i>
                         </button>
                         <div data-transition="" data-selector=".show"
                             data-enter="transition-all ease-linear duration-150"
                             data-enter-from="absolute !mt-5 invisible opacity-0 translate-y-1"
                             data-enter-to="!mt-1 visible opacity-100 translate-y-0"
                             data-leave="transition-all ease-linear duration-150"
                             data-leave-from="!mt-1 visible opacity-100 translate-y-0"
                             data-leave-to="absolute !mt-5 invisible opacity-0 translate-y-1"
                             class="dropdown-menu absolute z-[9999] hidden">
                             <div data-tw-merge=""
                                 class="dropdown-content rounded-md border-transparent bg-white p-2 shadow-[0px_3px_10px_#00000017] dark:border-transparent dark:bg-darkmode-600 w-40">
                                 <a
                                     class="cursor-pointer flex items-center p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-200/60 dark:bg-darkmode-600 dark:hover:bg-darkmode-400 dropdown-item"><i
                                         data-tw-merge="" data-lucide="copy" class="stroke-[1] mr-2 h-4 w-4"></i>
                                     Copy Link</a>
                                 <a
                                     class="cursor-pointer flex items-center p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-200/60 dark:bg-darkmode-600 dark:hover:bg-darkmode-400 dropdown-item"><i
                                         data-tw-merge="" data-lucide="trash" class="stroke-[1] mr-2 h-4 w-4"></i>
                                     Delete</a>
                             </div>
                         </div>
                     </div>
                     <div class="flex items-center">
                         <div
                             class="h-[54px] w-[54px] cursor-pointer rounded-full border border-primary/80 bg-slate-50 p-0.5">
                             <div class="h-full w-full rounded-full border border-slate-300/70 bg-white p-1">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%"
                                     viewBox="0 0 1503 1504" fill="none">
                                     <rect x="287" y="258" width="928" height="844" fill="white" />
                                     <path fill-rule="evenodd" clip-rule="evenodd"
                                         d="M1502.5 752C1502.5 1166.77 1166.27 1503 751.5 1503C336.734 1503 0.5 1166.77 0.5 752C0.5 337.234 336.734 1 751.5 1C1166.27 1 1502.5 337.234 1502.5 752ZM538.688 1050.86H392.94C362.314 1050.86 347.186 1050.86 337.962 1044.96C327.999 1038.5 321.911 1027.8 321.173 1015.99C320.619 1005.11 328.184 991.822 343.312 965.255L703.182 330.935C718.495 303.999 726.243 290.531 736.021 285.55C746.537 280.2 759.083 280.2 769.599 285.55C779.377 290.531 787.126 303.999 802.438 330.935L876.42 460.079L876.797 460.738C893.336 489.635 901.723 504.289 905.385 519.669C909.443 536.458 909.443 554.169 905.385 570.958C901.695 586.455 893.393 601.215 876.604 630.549L687.573 964.702L687.084 965.558C670.436 994.693 661.999 1009.46 650.306 1020.6C637.576 1032.78 622.263 1041.63 605.474 1046.62C590.161 1050.86 573.004 1050.86 538.688 1050.86ZM906.75 1050.86H1115.59C1146.4 1050.86 1161.9 1050.86 1171.13 1044.78C1181.09 1038.32 1187.36 1027.43 1187.92 1015.63C1188.45 1005.1 1181.05 992.33 1166.55 967.307C1166.05 966.455 1165.55 965.588 1165.04 964.706L1060.43 785.75L1059.24 783.735C1044.54 758.877 1037.12 746.324 1027.59 741.472C1017.08 736.121 1004.71 736.121 994.199 741.472C984.605 746.453 976.857 759.552 961.544 785.934L857.306 964.891L856.949 965.507C841.69 991.847 834.064 1005.01 834.614 1015.81C835.352 1027.62 841.44 1038.5 851.402 1044.96C860.443 1050.86 875.94 1050.86 906.75 1050.86Z"
                                         fill="#E84142" />
                                 </svg>
                             </div>
                         </div>
                         <div class="ml-4">
                             <div class="-mt-0.5 text-lg font-medium text-primary">
                                 Avalanche
                             </div>
                             <div class="mt-0.5 text-slate-500">AVAX/USDT</div>
                         </div>
                     </div>
                     <div
                         class="box mt-16 rounded-[0.6rem] border border-dashed border-slate-300/80 px-4 py-2.5 shadow-sm">
                         <div class="flex items-center">
                             <div class="text-xl font-medium leading-tight">
                                 4,125,15
                             </div>
                             <div class="ml-2.5 flex items-center font-medium text-success">
                                 +4.5%
                                 <i data-tw-merge="" data-lucide="chevron-up" class="ml-px h-4 w-4 stroke-[1.5]"></i>
                             </div>
                         </div>
                         <div class="mt-1 text-base text-slate-500">$441,051,00</div>
                     </div>
                 </div>
                 <div class="box box--stacked col-span-12 flex flex-col p-5 sm:col-span-6 xl:col-span-3">
                     <div data-tw-merge="" data-tw-placement="bottom-end"
                         class="dropdown absolute right-0 top-0 mr-5 mt-5"><button data-tw-toggle="dropdown"
                             aria-expanded="false" class="cursor-pointer h-5 w-5 text-slate-500"><i data-tw-merge=""
                                 data-lucide="more-vertical"
                                 class="stroke-[1] h-6 w-6 fill-slate-400/70 stroke-slate-400/70"></i>
                         </button>
                         <div data-transition="" data-selector=".show"
                             data-enter="transition-all ease-linear duration-150"
                             data-enter-from="absolute !mt-5 invisible opacity-0 translate-y-1"
                             data-enter-to="!mt-1 visible opacity-100 translate-y-0"
                             data-leave="transition-all ease-linear duration-150"
                             data-leave-from="!mt-1 visible opacity-100 translate-y-0"
                             data-leave-to="absolute !mt-5 invisible opacity-0 translate-y-1"
                             class="dropdown-menu absolute z-[9999] hidden">
                             <div data-tw-merge=""
                                 class="dropdown-content rounded-md border-transparent bg-white p-2 shadow-[0px_3px_10px_#00000017] dark:border-transparent dark:bg-darkmode-600 w-40">
                                 <a
                                     class="cursor-pointer flex items-center p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-200/60 dark:bg-darkmode-600 dark:hover:bg-darkmode-400 dropdown-item"><i
                                         data-tw-merge="" data-lucide="copy" class="stroke-[1] mr-2 h-4 w-4"></i>
                                     Copy Link</a>
                                 <a
                                     class="cursor-pointer flex items-center p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-200/60 dark:bg-darkmode-600 dark:hover:bg-darkmode-400 dropdown-item"><i
                                         data-tw-merge="" data-lucide="trash" class="stroke-[1] mr-2 h-4 w-4"></i>
                                     Delete</a>
                             </div>
                         </div>
                     </div>
                     <div class="flex items-center">
                         <div
                             class="h-[54px] w-[54px] cursor-pointer rounded-full border border-primary/80 bg-slate-50 p-0.5">
                             <div class="h-full w-full rounded-full border border-slate-300/70 bg-white p-1">
                                 <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 336.41 337.42">
                                     <defs />
                                     <title>Asset 1</title>
                                     <g data-name="Layer 2">
                                         <g data-name="Layer 1">
                                             <path fill="#f0b90b" d="M168.2.71l41.5,42.5L105.2,147.71l-41.5-41.5Z" />
                                             <path fill="#f0b90b"
                                                 d="M231.2,63.71l41.5,42.5L105.2,273.71l-41.5-41.5Z" />
                                             <path fill="#f0b90b" d="M42.2,126.71l41.5,42.5-41.5,41.5L.7,169.21Z" />
                                             <path fill="#f0b90b"
                                                 d="M294.2,126.71l41.5,42.5L168.2,336.71l-41.5-41.5Z" />
                                         </g>
                                     </g>
                                 </svg>
                             </div>
                         </div>
                         <div class="ml-4">
                             <div class="-mt-0.5 text-lg font-medium text-primary">
                                 Binance
                             </div>
                             <div class="mt-0.5 text-slate-500">BUSD</div>
                         </div>
                     </div>
                     <div
                         class="box mt-16 rounded-[0.6rem] border border-dashed border-slate-300/80 px-4 py-2.5 shadow-sm">
                         <div class="flex items-center">
                             <div class="text-xl font-medium leading-tight">34,49</div>
                             <div class="ml-2.5 flex items-center font-medium text-success">
                                 +1.5%
                                 <i data-tw-merge="" data-lucide="chevron-up" class="ml-px h-4 w-4 stroke-[1.5]"></i>
                             </div>
                         </div>
                         <div class="mt-1 text-base text-slate-500">$21,910,00</div>
                     </div>
                 </div>
             </div>
         </div>
         <div class="col-span-12 flex flex-col gap-y-10 xl:col-span-4">
             <div>
                 <div class="flex h-10 items-center">
                     <div class="text-base font-medium">Market</div>
                 </div>
                 <div class="box box--stacked mt-3.5 p-5">
                     <div class="mb-5 border-b border-dashed pb-5">
                         <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                             <div class="flex items-center">
                                 <div
                                     class="h-11 w-11 cursor-pointer rounded-full border border-primary/70 bg-slate-50 p-0.5">
                                     <div class="h-full w-full rounded-full border border-slate-300/70 bg-white p-1">
                                         <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%"
                                             viewBox="0 0 1503 1504" fill="none">
                                             <rect x="287" y="258" width="928" height="844" fill="white" />
                                             <path fill-rule="evenodd" clip-rule="evenodd"
                                                 d="M1502.5 752C1502.5 1166.77 1166.27 1503 751.5 1503C336.734 1503 0.5 1166.77 0.5 752C0.5 337.234 336.734 1 751.5 1C1166.27 1 1502.5 337.234 1502.5 752ZM538.688 1050.86H392.94C362.314 1050.86 347.186 1050.86 337.962 1044.96C327.999 1038.5 321.911 1027.8 321.173 1015.99C320.619 1005.11 328.184 991.822 343.312 965.255L703.182 330.935C718.495 303.999 726.243 290.531 736.021 285.55C746.537 280.2 759.083 280.2 769.599 285.55C779.377 290.531 787.126 303.999 802.438 330.935L876.42 460.079L876.797 460.738C893.336 489.635 901.723 504.289 905.385 519.669C909.443 536.458 909.443 554.169 905.385 570.958C901.695 586.455 893.393 601.215 876.604 630.549L687.573 964.702L687.084 965.558C670.436 994.693 661.999 1009.46 650.306 1020.6C637.576 1032.78 622.263 1041.63 605.474 1046.62C590.161 1050.86 573.004 1050.86 538.688 1050.86ZM906.75 1050.86H1115.59C1146.4 1050.86 1161.9 1050.86 1171.13 1044.78C1181.09 1038.32 1187.36 1027.43 1187.92 1015.63C1188.45 1005.1 1181.05 992.33 1166.55 967.307C1166.05 966.455 1165.55 965.588 1165.04 964.706L1060.43 785.75L1059.24 783.735C1044.54 758.877 1037.12 746.324 1027.59 741.472C1017.08 736.121 1004.71 736.121 994.199 741.472C984.605 746.453 976.857 759.552 961.544 785.934L857.306 964.891L856.949 965.507C841.69 991.847 834.064 1005.01 834.614 1015.81C835.352 1027.62 841.44 1038.5 851.402 1044.96C860.443 1050.86 875.94 1050.86 906.75 1050.86Z"
                                                 fill="#E84142" />
                                         </svg>
                                     </div>
                                 </div>
                                 <div class="ml-4">
                                     <div class="relative">
                                         <select data-tw-merge=""
                                             class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full border-slate-200 rounded-md focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 group-[.form-inline]:flex-1 border-0 bg-none p-0 text-base font-medium text-primary shadow-none focus:ring-0">
                                             <option value="avalanche">Avalanche</option>
                                             <option value="bitcoin">Bitcoin</option>
                                             <option value="ethereum">Ethereum</option>
                                             <option value="binance">Binance</option>
                                         </select>
                                         <i data-tw-merge="" data-lucide="chevron-down"
                                             class="absolute inset-y-0 right-0 my-auto -mr-5 h-4 w-4 stroke-[1.3]"></i>
                                     </div>
                                     <div class="mt-0.5 text-xs text-slate-500">
                                         AVAX/USDT
                                     </div>
                                 </div>
                             </div>
                             <div class="sm:ml-auto">
                                 <select data-tw-merge=""
                                     class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 group-[.form-inline]:flex-1">
                                     <option value="daily">24 Hours</option>
                                     <option value="weekly">48 Hours</option>
                                     <option value="monthly">64 Hours</option>
                                 </select>
                             </div>
                         </div>
                     </div>
                     <div>
                         <div class="text-slate-500">Avalanche Price</div>
                         <div class="mt-0.5 flex items-center">
                             <div class="text-lg font-medium">$1,342.02</div>
                             <div class="-mr-1 ml-2 flex items-center text-xs text-success">
                                 1.94%
                                 <i data-tw-merge="" data-lucide="chevron-up" class="stroke-[1] ml-px h-4 w-4"></i>
                             </div>
                         </div>
                     </div>
                     <div class="mt-4 flex flex-col rounded-[0.6rem] border border-dashed border-slate-300/80">
                         <div
                             class="flex items-center border-b border-dashed border-slate-300/80 px-3.5 py-2.5 last:border-0">
                             <div>
                                 <div class="flex items-center whitespace-nowrap text-slate-500">
                                     Low
                                     <div data-placement="top" title="Low" class="tooltip cursor-pointer ml-1.5">
                                         <i data-tw-merge="" data-lucide="info"
                                             class="stroke-[1] h-3.5 w-3.5 text-slate-500/70"></i>
                                     </div>
                                 </div>
                                 <div class="mt-1 whitespace-nowrap text-base font-medium text-slate-600">
                                     $2,367,01
                                 </div>
                             </div>
                         </div>
                         <div
                             class="flex items-center border-b border-dashed border-slate-300/80 px-3.5 py-2.5 last:border-0">
                             <div>
                                 <div class="flex items-center whitespace-nowrap text-slate-500">
                                     High
                                     <div data-placement="top" title="High" class="tooltip cursor-pointer ml-1.5">
                                         <i data-tw-merge="" data-lucide="info"
                                             class="stroke-[1] h-3.5 w-3.5 text-slate-500/70"></i>
                                     </div>
                                 </div>
                                 <div class="mt-1 whitespace-nowrap text-base font-medium text-slate-600">
                                     $4,187,02
                                 </div>
                             </div>
                         </div>
                     </div>
                     <div class="mt-6 font-medium">Key Stats</div>
                     <div class="mt-4 flex flex-col rounded-[0.6rem] border border-dashed border-slate-300/80">
                         <div
                             class="flex items-center border-b border-dashed border-slate-300/80 px-3.5 py-2.5 last:border-0">
                             <div>
                                 <div class="flex items-center whitespace-nowrap text-slate-500">
                                     Market Cap
                                     <div data-placement="top" title="Market Cap"
                                         class="tooltip cursor-pointer ml-1.5"><i data-tw-merge="" data-lucide="info"
                                             class="stroke-[1] h-3.5 w-3.5 text-slate-500/70"></i>
                                     </div>
                                 </div>
                                 <div class="mt-1 whitespace-nowrap text-base font-medium text-slate-600">
                                     $157,479,048,41
                                 </div>
                             </div>
                             <div class="ml-auto">
                                 <div class="-mr-1 ml-2 flex items-center text-xs text-success">
                                     4.94%
                                     <i data-tw-merge="" data-lucide="chevron-up"
                                         class="stroke-[1] ml-px h-4 w-4"></i>
                                 </div>
                             </div>
                         </div>
                         <div
                             class="flex items-center border-b border-dashed border-slate-300/80 px-3.5 py-2.5 last:border-0">
                             <div>
                                 <div class="flex items-center whitespace-nowrap text-slate-500">
                                     Fully Diluted Market Cap
                                     <div data-placement="top" title="Fully Diluted Market Cap"
                                         class="tooltip cursor-pointer ml-1.5"><i data-tw-merge="" data-lucide="info"
                                             class="stroke-[1] h-3.5 w-3.5 text-slate-500/70"></i>
                                     </div>
                                 </div>
                                 <div class="mt-1 whitespace-nowrap text-base font-medium text-slate-600">
                                     $297,479,048,41
                                 </div>
                             </div>
                             <div class="ml-auto">
                                 <div class="-mr-1 ml-2 flex items-center text-xs text-success">
                                     2.94%
                                     <i data-tw-merge="" data-lucide="chevron-up"
                                         class="stroke-[1] ml-px h-4 w-4"></i>
                                 </div>
                             </div>
                         </div>
                         <div
                             class="flex items-center border-b border-dashed border-slate-300/80 px-3.5 py-2.5 last:border-0">
                             <div>
                                 <div class="flex items-center whitespace-nowrap text-slate-500">
                                     Volume
                                     <span
                                         class="ml-1.5 rounded-md border bg-slate-100/80 px-1.5 py-px text-xs text-slate-500">
                                         24h
                                     </span>
                                     <div data-placement="top" title="Volume" class="tooltip cursor-pointer ml-1.5">
                                         <i data-tw-merge="" data-lucide="info"
                                             class="stroke-[1] h-3.5 w-3.5 text-slate-500/70"></i>
                                     </div>
                                 </div>
                                 <div class="mt-1 whitespace-nowrap text-base font-medium text-slate-600">
                                     $24,479,048,41
                                 </div>
                             </div>
                             <div class="ml-auto">
                                 <div class="-mr-1 ml-2 flex items-center text-xs text-danger">
                                     3.74%
                                     <i data-tw-merge="" data-lucide="chevron-down"
                                         class="stroke-[1] ml-px h-4 w-4"></i>
                                 </div>
                             </div>
                         </div>
                         <div
                             class="flex items-center border-b border-dashed border-slate-300/80 px-3.5 py-2.5 last:border-0">
                             <div>
                                 <div class="flex items-center whitespace-nowrap text-slate-500">
                                     Circulating Supply
                                     <div data-placement="top" title="Circulating Supply"
                                         class="tooltip cursor-pointer ml-1.5"><i data-tw-merge="" data-lucide="info"
                                             class="stroke-[1] h-3.5 w-3.5 text-slate-500/70"></i>
                                     </div>
                                 </div>
                                 <div class="mt-1 whitespace-nowrap text-base font-medium text-slate-600">
                                     $157,479,048,41
                                 </div>
                             </div>
                         </div>
                     </div>
                     <button data-tw-merge=""
                         class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed text-primary dark:border-primary [&:hover:not(:disabled)]:bg-primary/10 mt-6 w-full border-primary/50"><i
                             data-tw-merge="" data-lucide="external-link" class="mr-2 h-4 w-4 stroke-[1.3]"></i>
                         Buy Avalanche</button>
                 </div>
             </div>
         </div>
         <div class="col-span-12 flex flex-col gap-y-10 md:col-span-6 xl:col-span-4">
             <div>
                 <div class="flex h-10 items-center">
                     <div class="text-base font-medium">Withdrawal</div>
                 </div>
                 <div class="box box--stacked mt-3.5 p-5">
                     <div
                         class="flex flex-col overflow-hidden rounded-[0.6rem] border border-dashed border-slate-300/80">
                         <div
                             class="flex items-center border-b border-dashed border-slate-300/80 bg-slate-50/80 px-3.5 py-2.5 last:border-0">
                             <div class="w-full">
                                 <div class="flex items-center whitespace-nowrap text-slate-500">
                                     Currency
                                 </div>
                                 <div class="mt-1.5 flex w-full items-center">
                                     <div class="mr-2.5">
                                         <div
                                             class="h-8 w-8 cursor-pointer rounded-full border border-slate-300/70 bg-white p-0.5">
                                             <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%"
                                                 version="1.1" shape-rendering="geometricPrecision"
                                                 text-rendering="geometricPrecision" image-rendering="optimizeQuality"
                                                 fill-rule="evenodd" clip-rule="evenodd"
                                                 viewBox="0 0 4091.27 4091.73">
                                                 <g>
                                                     <metadata />
                                                     <g>
                                                         <path fill="#F7931A" fill-rule="nonzero"
                                                             d="M4030.06 2540.77c-273.24,1096.01 -1383.32,1763.02 -2479.46,1489.71 -1095.68,-273.24 -1762.69,-1383.39 -1489.33,-2479.31 273.12,-1096.13 1383.2,-1763.19 2479,-1489.95 1096.06,273.24 1763.03,1383.51 1489.76,2479.57l0.02 -0.02z" />
                                                         <path fill="white" fill-rule="nonzero"
                                                             d="M2947.77 1754.38c40.72,-272.26 -166.56,-418.61 -450,-516.24l91.95 -368.8 -224.5 -55.94 -89.51 359.09c-59.02,-14.72 -119.63,-28.59 -179.87,-42.34l90.16 -361.46 -224.36 -55.94 -92 368.68c-48.84,-11.12 -96.81,-22.11 -143.35,-33.69l0.26 -1.16 -309.59 -77.31 -59.72 239.78c0,0 166.56,38.18 163.05,40.53 90.91,22.69 107.35,82.87 104.62,130.57l-104.74 420.15c6.26,1.59 14.38,3.89 23.34,7.49 -7.49,-1.86 -15.46,-3.89 -23.73,-5.87l-146.81 588.57c-11.11,27.62 -39.31,69.07 -102.87,53.33 2.25,3.26 -163.17,-40.72 -163.17,-40.72l-111.46 256.98 292.15 72.83c54.35,13.63 107.61,27.89 160.06,41.3l-92.9 373.03 224.24 55.94 92 -369.07c61.26,16.63 120.71,31.97 178.91,46.43l-91.69 367.33 224.51 55.94 92.89 -372.33c382.82,72.45 670.67,43.24 791.83,-303.02 97.63,-278.78 -4.86,-439.58 -206.26,-544.44 146.69,-33.83 257.18,-130.31 286.64,-329.61l-0.07 -0.05zm-512.93 719.26c-69.38,278.78 -538.76,128.08 -690.94,90.29l123.28 -494.2c152.17,37.99 640.17,113.17 567.67,403.91zm69.43 -723.3c-63.29,253.58 -453.96,124.75 -580.69,93.16l111.77 -448.21c126.73,31.59 534.85,90.55 468.94,355.05l-0.02 0z" />
                                                     </g>
                                                 </g>
                                             </svg>
                                         </div>
                                     </div>
                                     <select data-tw-merge=""
                                         class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full border-slate-200 rounded-md py-2 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 group-[.form-inline]:flex-1 border-0 bg-transparent px-0 text-base font-medium shadow-none focus:ring-0">
                                         <option value="bitcoin">Bitcoin (BTC)</option>
                                         <option value="avalanche">Avalanche (AVAX)</option>
                                         <option value="ethereum">Ethereum (ETH)</option>
                                         <option value="binance">Binance (BUSD)</option>
                                     </select>
                                 </div>
                             </div>
                         </div>
                         <div
                             class="flex items-center border-b border-dashed border-slate-300/80 bg-slate-50/80 px-3.5 py-2.5 last:border-0">
                             <div class="w-full">
                                 <div class="flex items-center whitespace-nowrap text-slate-500">
                                     Amount
                                 </div>
                                 <div class="relative mt-1.5">
                                     <input data-tw-merge="" type="text" value="945.03"
                                         class="disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full border-slate-200 rounded-md placeholder:text-slate-400/90 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80 [&[type='file']]:border file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:border-r-[1px] file:border-slate-100/10 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-500/70 hover:file:bg-200 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10 border-0 bg-transparent pl-0 text-base font-medium shadow-none focus:ring-0">
                                     <span
                                         class="absolute inset-y-0 right-0 my-auto mr-1.5 flex h-6 items-center rounded-md border bg-slate-100 px-1.5 py-0.5 text-xs text-slate-500">
                                         MAX
                                     </span>
                                 </div>
                             </div>
                         </div>
                     </div>
                     <div class="mt-6 flex items-center whitespace-nowrap text-slate-500">
                         Address
                         <div data-placement="top" title="Low" class="tooltip cursor-pointer ml-1.5"><i
                                 data-tw-merge="" data-lucide="info"
                                 class="stroke-[1] h-3.5 w-3.5 text-slate-500/70"></i></div>
                     </div>
                     <div class="relative mt-2 flex gap-3">
                         <input data-tw-merge="" type="text" value="0x41c087859869703Fa234d"
                             class="disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80 [&[type='file']]:border file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:border-r-[1px] file:border-slate-100/10 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-500/70 hover:file:bg-200 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10 bg-slate-50/80">
                         <button data-tw-merge="" data-placement="top" title="Copy link"
                             class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-secondary text-slate-500 dark:border-darkmode-100/40 dark:text-slate-300 [&:hover:not(:disabled)]:bg-secondary/20 [&:hover:not(:disabled)]:dark:bg-darkmode-100/10 tooltip"><i
                                 data-tw-merge="" data-lucide="copy" class="h-4 w-4 stroke-[1.5]"></i></button>
                     </div>
                 </div>
             </div>
             <div>
                 <div class="flex h-10 items-center">
                     <div class="text-base font-medium">Exchange</div>
                 </div>
                 <div class="box box--stacked mt-3.5 p-5">
                     <div class="mb-5 border-b border-dashed pb-5">
                         <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                             <div class="font-medium">1 ETH = $1,308,02</div>
                             <div class="sm:ml-auto">
                                 <select data-tw-merge=""
                                     class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 group-[.form-inline]:flex-1">
                                     <option value="market">Market</option>
                                 </select>
                             </div>
                         </div>
                     </div>
                     <div class="mt-1">
                         <ul data-tw-merge="" role="tablist"
                             class="p-0.5 border dark:border-darkmode-400 w-full flex rounded-[0.6rem] border-slate-200 bg-white shadow-sm">
                             <li id="example-1-tab" data-tw-merge="" role="presentation"
                                 class="focus-visible:outline-none flex-1 bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem] [&[aria-selected='true']_button]:text-current">
                                 <button data-tw-merge="" data-tw-target="#example-1" role="tab"
                                     class="cursor-pointer appearance-none px-3 border border-transparent transition-colors dark:text-slate-400 [&.active]:text-slate-700 py-1.5 dark:border-transparent [&.active]:border [&.active]:shadow-sm [&.active]:font-medium [&.active]:border-slate-200 [&.active]:bg-white [&.active]:dark:text-slate-300 [&.active]:dark:bg-darkmode-400 [&.active]:dark:border-darkmode-400 active flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] text-slate-500"><i
                                         data-tw-merge="" data-lucide="file-line-chart"
                                         class="mr-2 h-4 w-4 stroke-[1.3]"></i>
                                     Floating</button>
                             </li>
                             <li id="example-2-tab" data-tw-merge="" role="presentation"
                                 class="focus-visible:outline-none flex-1 bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem] [&[aria-selected='true']_button]:text-current">
                                 <button data-tw-merge="" data-tw-target="#example-2" role="tab"
                                     class="cursor-pointer appearance-none px-3 border border-transparent transition-colors dark:text-slate-400 [&.active]:text-slate-700 py-1.5 dark:border-transparent [&.active]:border [&.active]:shadow-sm [&.active]:font-medium [&.active]:border-slate-200 [&.active]:bg-white [&.active]:dark:text-slate-300 [&.active]:dark:bg-darkmode-400 [&.active]:dark:border-darkmode-400 flex w-full items-center justify-center whitespace-nowrap rounded-[0.6rem] text-slate-500"><i
                                         data-tw-merge="" data-lucide="lock" class="mr-2 h-4 w-4 stroke-[1.3]"></i>
                                     Fixed</button>
                             </li>
                         </ul>
                         <div class="tab-content mt-6">
                             <div data-transition="" data-selector=".active"
                                 data-enter="transition-[visibility,opacity] ease-linear duration-150"
                                 data-enter-from="!p-0 !h-0 overflow-hidden invisible opacity-0"
                                 data-enter-to="visible opacity-100"
                                 data-leave="transition-[visibility,opacity] ease-linear duration-150"
                                 data-leave-from="visible opacity-100"
                                 data-leave-to="!p-0 !h-0 overflow-hidden invisible opacity-0" id="example-1"
                                 role="tabpanel" aria-labelledby="example-1-tab" class="tab-pane active">
                                 <div
                                     class="flex flex-col overflow-hidden rounded-[0.6rem] border border-dashed border-slate-300/80">
                                     <div
                                         class="flex items-center border-b border-dashed border-slate-300/80 bg-slate-50/80 px-3.5 py-2.5 last:border-0">
                                         <div class="w-full">
                                             <div class="flex items-center whitespace-nowrap text-slate-500">
                                                 You Send
                                             </div>
                                             <div class="relative mt-1.5">
                                                 <input data-tw-merge="" type="text" value="9.03"
                                                     class="disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full border-slate-200 rounded-md placeholder:text-slate-400/90 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80 [&[type='file']]:border file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:border-r-[1px] file:border-slate-100/10 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-500/70 hover:file:bg-200 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10 border-0 bg-transparent pl-0 text-base font-medium shadow-none focus:ring-0">
                                                 <span
                                                     class="absolute inset-y-0 right-0 my-auto mr-1.5 flex h-6 items-center rounded-md border bg-slate-100 px-1.5 py-0.5 text-xs text-slate-500">
                                                     ETH
                                                 </span>
                                             </div>
                                         </div>
                                     </div>
                                     <div
                                         class="flex items-center border-b border-dashed border-slate-300/80 bg-slate-50/80 px-3.5 py-2.5 last:border-0">
                                         <div class="w-full">
                                             <div class="flex items-center whitespace-nowrap text-slate-500">
                                                 You Receive
                                             </div>
                                             <div class="relative mt-1.5">
                                                 <input data-tw-merge="" type="text" value="00001.03"
                                                     class="disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full border-slate-200 rounded-md placeholder:text-slate-400/90 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80 [&[type='file']]:border file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:border-r-[1px] file:border-slate-100/10 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-500/70 hover:file:bg-200 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10 border-0 bg-transparent pl-0 text-base font-medium shadow-none focus:ring-0">
                                                 <span
                                                     class="absolute inset-y-0 right-0 my-auto mr-1.5 flex h-6 items-center rounded-md border bg-slate-100 px-1.5 py-0.5 text-xs text-slate-500">
                                                     BTC
                                                 </span>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                                 <div class="mt-6 flex items-center whitespace-nowrap text-slate-500">
                                     Address
                                     <div data-placement="top" title="Low" class="tooltip cursor-pointer ml-1.5">
                                         <i data-tw-merge="" data-lucide="info"
                                             class="stroke-[1] h-3.5 w-3.5 text-slate-500/70"></i>
                                     </div>
                                 </div>
                                 <div class="relative mt-2 flex gap-3">
                                     <input data-tw-merge="" type="text" value="0x41c087859869703Fa234d"
                                         class="disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80 [&[type='file']]:border file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:border-r-[1px] file:border-slate-100/10 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-500/70 hover:file:bg-200 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10 bg-slate-50/80">
                                     <button data-tw-merge="" data-placement="top" title="Copy link"
                                         class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-secondary text-slate-500 dark:border-darkmode-100/40 dark:text-slate-300 [&:hover:not(:disabled)]:bg-secondary/20 [&:hover:not(:disabled)]:dark:bg-darkmode-100/10 tooltip"><i
                                             data-tw-merge="" data-lucide="copy"
                                             class="h-4 w-4 stroke-[1.5]"></i></button>
                                 </div>
                                 <button data-tw-merge=""
                                     class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary text-white dark:border-primary mt-6 w-full border-primary/50"><i
                                         data-tw-merge="" data-lucide="arrow-right-left"
                                         class="mr-2 h-4 w-4 stroke-[1.3]"></i>
                                     Start Exchange</button>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
         <div class="col-span-12 flex flex-col gap-y-10 md:col-span-6 xl:col-span-4">
             <div>
                 <div class="flex h-10 items-center">
                     <div class="text-base font-medium">Account</div>
                 </div>
                 <div class="box box--stacked mt-3.5 p-5">
                     <div class="mb-5 flex items-center border-b border-dashed pb-5">
                         <div class="image-fit zoom-in h-10 w-10">
                             <img class="rounded-full shadow-[0px_0px_0px_2px_#fff,_1px_1px_5px_rgba(0,0,0,0.32)] dark:shadow-[0px_0px_0px_2px_#3f4865,_1px_1px_5px_rgba(0,0,0,0.32)]"
                                 src="dist/images/users/user8-50x50.jpg" alt="Tailwise - Admin Dashboard Template">
                         </div>
                         <div class="ml-3.5">
                             <div class="flex items-center">
                                 <span class="mr-4 font-medium">
                                     0x41c0878598697...234d
                                 </span>
                                 <a href="">
                                     <i data-tw-merge="" data-lucide="external-link"
                                         class="mr-2 h-4 w-4 stroke-[1.3]"></i>
                                 </a>
                                 <a href="">
                                     <i data-tw-merge="" data-lucide="copy" class="h-4 w-4 stroke-[1.3]"></i>
                                 </a>
                             </div>
                             <div class="mt-0.5 text-xs text-slate-500">MetaMask</div>
                         </div>
                     </div>
                     <div class="font-medium">Slippage Tolerance</div>
                     <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                         <div class="flex flex-1 items-center rounded-lg border bg-slate-50/80">
                             <a class="flex-1 border-r border-dashed bg-slate-100 px-3 py-2 text-center text-slate-500 last:border-r-0 hover:bg-slate-100"
                                 href="">
                                 1.5%
                             </a>
                             <a class="flex-1 border-r border-dashed px-3 py-2 text-center text-slate-500 last:border-r-0 hover:bg-slate-100"
                                 href="">
                                 2.0%
                             </a>
                             <a class="flex-1 border-r border-dashed px-3 py-2 text-center text-slate-500 last:border-r-0 hover:bg-slate-100"
                                 href="">
                                 2.5%
                             </a>
                             <a class="flex-1 border-r border-dashed px-3 py-2 text-center text-slate-500 last:border-r-0 hover:bg-slate-100"
                                 href="">
                                 3%
                             </a>
                         </div>
                         <div class="relative">
                             <input data-tw-merge="" type="text" value="5"
                                 class="disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80 [&[type='file']]:border file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:border-r-[1px] file:border-slate-100/10 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-500/70 hover:file:bg-200 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10 pr-11 text-right sm:w-24">
                             <div
                                 class="absolute inset-y-0 right-0 my-2 mr-3 flex items-center justify-center border-l pl-2.5 text-xs font-medium">
                                 %
                             </div>
                         </div>
                     </div>
                     <button data-tw-merge=""
                         class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed mt-3.5 w-full border-dashed border-slate-300 hover:bg-slate-50"><i
                             data-tw-merge="" data-lucide="cloud-off" class="mr-2 h-4 w-4 stroke-[1.3]"></i>
                         Disconnect</button>
                 </div>
             </div>
             <div>
                 <div class="flex h-10 items-center">
                     <div class="text-base font-medium">Recent Transactions</div>
                 </div>
                 <div class="box box--stacked mt-3.5 p-5">
                     <div
                         class="mb-3.5 flex items-center border-b border-dashed pb-3.5 last:mb-0 last:border-0 last:pb-0">
                         <div>
                             <div
                                 class="h-10 w-10 cursor-pointer rounded-full border border-primary/80 bg-slate-50 p-0.5">
                                 <div class="h-full w-full rounded-full border border-slate-300/70 bg-white p-1">
                                     <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%"
                                         viewBox="0 0 1503 1504" fill="none">
                                         <rect x="287" y="258" width="928" height="844" fill="white" />
                                         <path fill-rule="evenodd" clip-rule="evenodd"
                                             d="M1502.5 752C1502.5 1166.77 1166.27 1503 751.5 1503C336.734 1503 0.5 1166.77 0.5 752C0.5 337.234 336.734 1 751.5 1C1166.27 1 1502.5 337.234 1502.5 752ZM538.688 1050.86H392.94C362.314 1050.86 347.186 1050.86 337.962 1044.96C327.999 1038.5 321.911 1027.8 321.173 1015.99C320.619 1005.11 328.184 991.822 343.312 965.255L703.182 330.935C718.495 303.999 726.243 290.531 736.021 285.55C746.537 280.2 759.083 280.2 769.599 285.55C779.377 290.531 787.126 303.999 802.438 330.935L876.42 460.079L876.797 460.738C893.336 489.635 901.723 504.289 905.385 519.669C909.443 536.458 909.443 554.169 905.385 570.958C901.695 586.455 893.393 601.215 876.604 630.549L687.573 964.702L687.084 965.558C670.436 994.693 661.999 1009.46 650.306 1020.6C637.576 1032.78 622.263 1041.63 605.474 1046.62C590.161 1050.86 573.004 1050.86 538.688 1050.86ZM906.75 1050.86H1115.59C1146.4 1050.86 1161.9 1050.86 1171.13 1044.78C1181.09 1038.32 1187.36 1027.43 1187.92 1015.63C1188.45 1005.1 1181.05 992.33 1166.55 967.307C1166.05 966.455 1165.55 965.588 1165.04 964.706L1060.43 785.75L1059.24 783.735C1044.54 758.877 1037.12 746.324 1027.59 741.472C1017.08 736.121 1004.71 736.121 994.199 741.472C984.605 746.453 976.857 759.552 961.544 785.934L857.306 964.891L856.949 965.507C841.69 991.847 834.064 1005.01 834.614 1015.81C835.352 1027.62 841.44 1038.5 851.402 1044.96C860.443 1050.86 875.94 1050.86 906.75 1050.86Z"
                                             fill="#E84142" />
                                     </svg>
                                 </div>
                             </div>
                         </div>
                         <div class="ml-3.5 w-full">
                             <div class="flex w-full items-center">
                                 <div class="mr-4 font-medium">Avalanche</div>
                                 <span class="ml-auto font-medium">412.10 AVAX</span>
                             </div>
                             <div class="mt-0.5 flex w-full items-center">
                                 <a class="text-xs text-primary" href="">
                                     Buy
                                 </a>
                                 <div class="ml-auto text-xs text-slate-500">
                                     Today, 14.25
                                 </div>
                             </div>
                         </div>
                     </div>
                     <div
                         class="mb-3.5 flex items-center border-b border-dashed pb-3.5 last:mb-0 last:border-0 last:pb-0">
                         <div>
                             <div
                                 class="h-10 w-10 cursor-pointer rounded-full border border-primary/80 bg-slate-50 p-0.5">
                                 <div class="h-full w-full rounded-full border border-slate-300/70 bg-white p-1">
                                     <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%"
                                         version="1.1" shape-rendering="geometricPrecision"
                                         text-rendering="geometricPrecision" image-rendering="optimizeQuality"
                                         fill-rule="evenodd" clip-rule="evenodd" viewBox="0 0 784.37 1277.39">
                                         <g>
                                             <metadata />
                                             <g>
                                                 <g>
                                                     <polygon fill="#343434" fill-rule="nonzero"
                                                         points="392.07,0 383.5,29.11 383.5,873.74 392.07,882.29 784.13,650.54 " />
                                                     <polygon fill="#8C8C8C" fill-rule="nonzero"
                                                         points="392.07,0 -0,650.54 392.07,882.29 392.07,472.33 " />
                                                     <polygon fill="#3C3C3B" fill-rule="nonzero"
                                                         points="392.07,956.52 387.24,962.41 387.24,1263.28 392.07,1277.38 784.37,724.89 " />
                                                     <polygon fill="#8C8C8C" fill-rule="nonzero"
                                                         points="392.07,1277.38 392.07,956.52 -0,724.89 " />
                                                     <polygon fill="#141414" fill-rule="nonzero"
                                                         points="392.07,882.29 784.13,650.54 392.07,472.33 " />
                                                     <polygon fill="#393939" fill-rule="nonzero"
                                                         points="0,650.54 392.07,882.29 392.07,472.33 " />
                                                 </g>
                                             </g>
                                         </g>
                                     </svg>
                                 </div>
                             </div>
                         </div>
                         <div class="ml-3.5 w-full">
                             <div class="flex w-full items-center">
                                 <div class="mr-4 font-medium">Ethereum</div>
                                 <span class="ml-auto font-medium">12.71 ETH</span>
                             </div>
                             <div class="mt-0.5 flex w-full items-center">
                                 <a class="text-xs text-primary" href="">
                                     Buy
                                 </a>
                                 <div class="ml-auto text-xs text-slate-500">
                                     Today, 01.00
                                 </div>
                             </div>
                         </div>
                     </div>
                     <div
                         class="mb-3.5 flex items-center border-b border-dashed pb-3.5 last:mb-0 last:border-0 last:pb-0">
                         <div>
                             <div
                                 class="h-10 w-10 cursor-pointer rounded-full border border-primary/80 bg-slate-50 p-0.5">
                                 <div class="h-full w-full rounded-full border border-slate-300/70 bg-white p-1">
                                     <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%"
                                         version="1.1" shape-rendering="geometricPrecision"
                                         text-rendering="geometricPrecision" image-rendering="optimizeQuality"
                                         fill-rule="evenodd" clip-rule="evenodd" viewBox="0 0 4091.27 4091.73">
                                         <g>
                                             <metadata />
                                             <g>
                                                 <path fill="#F7931A" fill-rule="nonzero"
                                                     d="M4030.06 2540.77c-273.24,1096.01 -1383.32,1763.02 -2479.46,1489.71 -1095.68,-273.24 -1762.69,-1383.39 -1489.33,-2479.31 273.12,-1096.13 1383.2,-1763.19 2479,-1489.95 1096.06,273.24 1763.03,1383.51 1489.76,2479.57l0.02 -0.02z" />
                                                 <path fill="white" fill-rule="nonzero"
                                                     d="M2947.77 1754.38c40.72,-272.26 -166.56,-418.61 -450,-516.24l91.95 -368.8 -224.5 -55.94 -89.51 359.09c-59.02,-14.72 -119.63,-28.59 -179.87,-42.34l90.16 -361.46 -224.36 -55.94 -92 368.68c-48.84,-11.12 -96.81,-22.11 -143.35,-33.69l0.26 -1.16 -309.59 -77.31 -59.72 239.78c0,0 166.56,38.18 163.05,40.53 90.91,22.69 107.35,82.87 104.62,130.57l-104.74 420.15c6.26,1.59 14.38,3.89 23.34,7.49 -7.49,-1.86 -15.46,-3.89 -23.73,-5.87l-146.81 588.57c-11.11,27.62 -39.31,69.07 -102.87,53.33 2.25,3.26 -163.17,-40.72 -163.17,-40.72l-111.46 256.98 292.15 72.83c54.35,13.63 107.61,27.89 160.06,41.3l-92.9 373.03 224.24 55.94 92 -369.07c61.26,16.63 120.71,31.97 178.91,46.43l-91.69 367.33 224.51 55.94 92.89 -372.33c382.82,72.45 670.67,43.24 791.83,-303.02 97.63,-278.78 -4.86,-439.58 -206.26,-544.44 146.69,-33.83 257.18,-130.31 286.64,-329.61l-0.07 -0.05zm-512.93 719.26c-69.38,278.78 -538.76,128.08 -690.94,90.29l123.28 -494.2c152.17,37.99 640.17,113.17 567.67,403.91zm69.43 -723.3c-63.29,253.58 -453.96,124.75 -580.69,93.16l111.77 -448.21c126.73,31.59 534.85,90.55 468.94,355.05l-0.02 0z" />
                                             </g>
                                         </g>
                                     </svg>
                                 </div>
                             </div>
                         </div>
                         <div class="ml-3.5 w-full">
                             <div class="flex w-full items-center">
                                 <div class="mr-4 font-medium">Bitcoin</div>
                                 <span class="ml-auto font-medium">21.10 BTC</span>
                             </div>
                             <div class="mt-0.5 flex w-full items-center">
                                 <a class="text-xs text-primary" href="">
                                     Buy
                                 </a>
                                 <div class="ml-auto text-xs text-slate-500">
                                     Today, 18.40
                                 </div>
                             </div>
                         </div>
                     </div>
                     <div
                         class="mb-3.5 flex items-center border-b border-dashed pb-3.5 last:mb-0 last:border-0 last:pb-0">
                         <div>
                             <div
                                 class="h-10 w-10 cursor-pointer rounded-full border border-primary/80 bg-slate-50 p-0.5">
                                 <div class="h-full w-full rounded-full border border-slate-300/70 bg-white p-1">
                                     <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 336.41 337.42">
                                         <defs />
                                         <title>Asset 1</title>
                                         <g data-name="Layer 2">
                                             <g data-name="Layer 1">
                                                 <path fill="#f0b90b"
                                                     d="M168.2.71l41.5,42.5L105.2,147.71l-41.5-41.5Z" />
                                                 <path fill="#f0b90b"
                                                     d="M231.2,63.71l41.5,42.5L105.2,273.71l-41.5-41.5Z" />
                                                 <path fill="#f0b90b"
                                                     d="M42.2,126.71l41.5,42.5-41.5,41.5L.7,169.21Z" />
                                                 <path fill="#f0b90b"
                                                     d="M294.2,126.71l41.5,42.5L168.2,336.71l-41.5-41.5Z" />
                                             </g>
                                         </g>
                                     </svg>
                                 </div>
                             </div>
                         </div>
                         <div class="ml-3.5 w-full">
                             <div class="flex w-full items-center">
                                 <div class="mr-4 font-medium">Binance</div>
                                 <span class="ml-auto font-medium">231.50 BUSD</span>
                             </div>
                             <div class="mt-0.5 flex w-full items-center">
                                 <a class="text-xs text-primary" href="">
                                     Buy
                                 </a>
                                 <div class="ml-auto text-xs text-slate-500">
                                     Today, 08.01
                                 </div>
                             </div>
                         </div>
                     </div>
                     <div
                         class="mb-3.5 flex items-center border-b border-dashed pb-3.5 last:mb-0 last:border-0 last:pb-0">
                         <div>
                             <div
                                 class="h-10 w-10 cursor-pointer rounded-full border border-primary/80 bg-slate-50 p-0.5">
                                 <div class="h-full w-full rounded-full border border-slate-300/70 bg-white p-1">
                                     <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%"
                                         viewBox="0 0 1503 1504" fill="none">
                                         <rect x="287" y="258" width="928" height="844" fill="white" />
                                         <path fill-rule="evenodd" clip-rule="evenodd"
                                             d="M1502.5 752C1502.5 1166.77 1166.27 1503 751.5 1503C336.734 1503 0.5 1166.77 0.5 752C0.5 337.234 336.734 1 751.5 1C1166.27 1 1502.5 337.234 1502.5 752ZM538.688 1050.86H392.94C362.314 1050.86 347.186 1050.86 337.962 1044.96C327.999 1038.5 321.911 1027.8 321.173 1015.99C320.619 1005.11 328.184 991.822 343.312 965.255L703.182 330.935C718.495 303.999 726.243 290.531 736.021 285.55C746.537 280.2 759.083 280.2 769.599 285.55C779.377 290.531 787.126 303.999 802.438 330.935L876.42 460.079L876.797 460.738C893.336 489.635 901.723 504.289 905.385 519.669C909.443 536.458 909.443 554.169 905.385 570.958C901.695 586.455 893.393 601.215 876.604 630.549L687.573 964.702L687.084 965.558C670.436 994.693 661.999 1009.46 650.306 1020.6C637.576 1032.78 622.263 1041.63 605.474 1046.62C590.161 1050.86 573.004 1050.86 538.688 1050.86ZM906.75 1050.86H1115.59C1146.4 1050.86 1161.9 1050.86 1171.13 1044.78C1181.09 1038.32 1187.36 1027.43 1187.92 1015.63C1188.45 1005.1 1181.05 992.33 1166.55 967.307C1166.05 966.455 1165.55 965.588 1165.04 964.706L1060.43 785.75L1059.24 783.735C1044.54 758.877 1037.12 746.324 1027.59 741.472C1017.08 736.121 1004.71 736.121 994.199 741.472C984.605 746.453 976.857 759.552 961.544 785.934L857.306 964.891L856.949 965.507C841.69 991.847 834.064 1005.01 834.614 1015.81C835.352 1027.62 841.44 1038.5 851.402 1044.96C860.443 1050.86 875.94 1050.86 906.75 1050.86Z"
                                             fill="#E84142" />
                                     </svg>
                                 </div>
                             </div>
                         </div>
                         <div class="ml-3.5 w-full">
                             <div class="flex w-full items-center">
                                 <div class="mr-4 font-medium">Avalanche</div>
                                 <span class="ml-auto font-medium">132.20 AVAX</span>
                             </div>
                             <div class="mt-0.5 flex w-full items-center">
                                 <a class="text-xs text-primary" href="">
                                     Buy
                                 </a>
                                 <div class="ml-auto text-xs text-slate-500">
                                     Today, 03.31
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </div>

</x-layouts::app>