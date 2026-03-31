<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-body-bg text-body">
        
        <!-- Start Preloader Area -->
        <div class="preloader" id="preloader">
            <div class="preloader">
                <div class="waviy position-relative">
                    <span class="d-inline-block">D</span>
                    <span class="d-inline-block">J</span>
                    <span class="d-inline-block">W</span>
                    <span class="d-inline-block">C</span>
                </div>
            </div>
        </div>
        <!-- End Preloader Area -->

        @include('partials.sidebar')

        <!-- Start Main Content Area -->
        <div class="container-fluid">
            <div class="main-content d-flex flex-column">
                
                @include('partials.header')

                <div class="main-content-container overflow-hidden">
                    {{ $slot }}
                </div>

                <div class="flex-grow-1"></div>

                <!-- Start Footer Area -->
                <footer class="footer-area bg-white text-center rounded-10 rounded-bottom-0">
                    <p class="fs-16 text-body">© {{ date('Y') }} <span class="text-secondary">{{ config('app.name') }}</span>. All rights reserved.</p>
                </footer>
                <!-- End Footer Area -->
            </div>
        </div>
        <!-- End Main Content Area -->

        @include('partials.scripts')
    </body>
</html>
