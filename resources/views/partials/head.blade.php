<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<!-- Links Of CSS File -->
<link rel="stylesheet" href="{{ asset('assets/css/sidebar-menu.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/simplebar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/prism.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/quill.snow.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/swiper-bundle.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/jsvectormap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

<!-- Favicon -->
<link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">

<!-- Title -->
<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
