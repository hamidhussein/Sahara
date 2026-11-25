@props([
    'title' => null,
    'description' => 'Sab Ka Sahara Welfare Foundation has served the people of Gilgit-Baltistan since 2006 with clean water, free health camps, ambulances, education support and food for families in need.',
    'donateBand' => true,
    'urdu' => false,
])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1e6b3a">
    <title>{{ $title ? $title.' | '.config('sswf.name') : config('sswf.name').' | Serving Gilgit-Baltistan since 2006' }}</title>
    <meta name="description" content="{{ $description }}">
    <meta property="og:site_name" content="{{ config('sswf.name') }}">
    <meta property="og:title" content="{{ $title ?? config('sswf.name') }}">
    <meta property="og:description" content="{{ $description }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    @fonts('inter')
    @if ($urdu)
        @fonts(['noto-nastaliq-urdu', 'noto-naskh-arabic'])
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex min-h-screen flex-col pb-16 lg:pb-0">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-button focus:bg-primary focus:px-4 focus:py-3 focus:text-white">
        Skip to content
    </a>

    <x-site.emergency-strip />
    <x-site.header />

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    @if ($donateBand)
        <x-site.donate-band />
    @endif

    <x-site.footer />
    <x-site.mobile-bar />

    @livewireScripts
</body>
</html>
