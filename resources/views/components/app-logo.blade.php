@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand href="{{ route('home') }}" {{ $attributes }}>
        <img src="{{ asset('assets/logo.png') }}" class="h-8 w-auto dark:hidden" alt="{{ config('app.name') }}">
        <img src="{{ asset('assets/logo.png') }}" class="hidden h-8 w-auto dark:block" alt="{{ config('app.name') }}">
    </flux:sidebar.brand>
@else
    <flux:brand href="{{ route('home') }}" {{ $attributes }}>
        <img src="{{ asset('assets/logo.png') }}" class="h-8 w-auto dark:hidden" alt="{{ config('app.name') }}">
        <img src="{{ asset('assets/logo.png') }}" class="hidden h-8 w-auto dark:block" alt="{{ config('app.name') }}">
    </flux:brand>
@endif
