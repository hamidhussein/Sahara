@props([
    'title' => null,
    'description' => null,
    'image' => null,        // absolute URL of the share image; falls back to the Settings default
    'type' => 'website',    // 'article' for news posts
    'noindex' => false,     // personal or utility pages that should stay out of search
    'donateBand' => true,
    'urdu' => false,
])
@php
    // SEO and sharing (plan 1.4): every page gets a title, description, share image and canonical URL.
    $siteName = config('sswf.name');
    $fullTitle = $title ? $title.' | '.$siteName : $siteName.' | Serving Gilgit-Baltistan since 2006';
    $metaDescription = \Illuminate\Support\Str::limit(trim(strip_tags($description ?: $site->seo_description)), 160);
    $shareImage = $image ?: $site->shareImageUrl();
    $canonical = url()->current();
    // Staging and local copies never get indexed; only the live site does.
    $hideFromSearch = $noindex || config('app.env') !== 'production';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1e6b3a">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonical }}">
    @if ($hideFromSearch)
        <meta name="robots" content="noindex, nofollow">
    @endif

    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:title" content="{{ $title ?? $siteName }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $shareImage }}">
    <meta property="og:locale" content="en_PK">
    <meta name="twitter:card" content="summary_large_image">
    {{ $head ?? '' }}

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
