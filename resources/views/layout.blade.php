<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="ltr"
    class="h-full"
>
<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>{{ $title ?? 'Visual Builder' }}</title>

    {{-- Preconnect for performance --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- Inter font for UI consistency --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Livewire Styles --}}
    @livewireStyles

    {{-- x-cloak support for Alpine.js --}}
    <style>
        [x-cloak] { display: none !important; }

        :root {
            --font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        }

        body {
            font-family: var(--font-family);
        }
    </style>

    {{-- Dark mode initialization (before content renders) --}}
    <script>
        (function() {
            const theme = localStorage.getItem('theme') ?? 'system';

            if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    {{-- Pre-built CSS shipped with ccast/tagixo (publish via:
         php artisan vendor:publish --tag=tagixo-assets --force) --}}
    <link rel="stylesheet" href="{{ asset('vendor/tagixo/tagixo.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/tagixo/builder-vendor.css') }}">

    {{-- Dynamic stylesheet updated by builder live preview --}}
    <style id="tagixo-dynamic-styles"></style>

    @stack('styles')
</head>

<body class="h-full bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased">
    {{-- Main content --}}
    {{ $slot }}

    {{-- Livewire Scripts (includes Alpine.js) --}}
    @livewireScripts

    {{-- Pre-built Vue SPA entry shipped with ccast/tagixo --}}
    <script type="module" src="{{ asset('vendor/tagixo/builder.js') }}"></script>

    @stack('scripts')
</body>
</html>
