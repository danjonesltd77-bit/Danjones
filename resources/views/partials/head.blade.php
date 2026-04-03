<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description"
    content="Echo Crypto Admin Dashboard is a professional, high-end platform for digital asset management, featuring real-time analytics and secure operations.">
<meta name="keywords" content="crypto, dashboard, admin, trading, wallet management, digital assets, echo, premium admin">
<meta name="author" content="DJWC">
<title>Echo - Crypto Admin Dashboard</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])

<!-- BEGIN: CSS Assets-->
<link rel="stylesheet" href="{{ asset('dist/css/vendors/tippy.css') }}">
<link rel="stylesheet" href="{{ asset('dist/css/vendors/simplebar.css') }}">
<link rel="stylesheet" href="{{ asset('dist/css/themes/echo.css') }}">
<link rel="stylesheet" href="{{ asset('dist/css/app.css') }}">
<!-- END: CSS Assets-->

<script>
    (function() {
        // Source of truth for theme evaluation
        const evaluateTheme = () => {
            const stored = localStorage.getItem('darkMode');
            return stored === 'true' || (stored === null && window.matchMedia('(prefers-color-scheme: dark)')
                .matches);
        };

        const applyTheme = () => {
            if (evaluateTheme()) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        };

        // 1. Initial Apply safely blocks FOUC
        applyTheme();

        // 2. Prevent Livewire from stripping the dark class during navigation morphs
        // Using MutationObserver as a sync backup
        new MutationObserver(mutations => {
            mutations.forEach(m => {
                if (m.attributeName === 'class') {
                    const isDark = evaluateTheme();
                    if (isDark && !document.documentElement.classList.contains('dark')) {
                        document.documentElement.classList.add('dark');
                    } else if (!isDark && document.documentElement.classList.contains('dark')) {
                        document.documentElement.classList.remove('dark');
                    }
                }
            });
        }).observe(document.documentElement, {
            attributes: true
        });

        // Force native Livewire morphdom to preserve the class before it even updates
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updating', ({
                el,
                toEl
            }) => {
                if (el.tagName.toLowerCase() === 'html' && document.documentElement.classList
                    .contains('dark')) {
                    toEl.classList.add('dark');
                }
            });
        });

        // 3. Alpine Store for UI toggles (only registers state and toggle action)
        document.addEventListener('alpine:init', () => {
            Alpine.store('darkMode', {
                on: evaluateTheme(),
                toggle() {
                    this.on = !this.on;
                    localStorage.setItem('darkMode', this.on);
                    applyTheme(); // Immediately update the HTML class
                }
            });
        });
    })();
</script>
