{{-- Title, one line and breadcrumb; over a photo with dark overlay once photos are supplied. --}}
@props(['title', 'lede' => null, 'image' => null, 'breadcrumbs' => []])
<section class="relative isolate overflow-hidden bg-primary-deep text-white">
    @if ($image)
        <img src="{{ $image }}" alt="" class="absolute inset-0 -z-10 size-full object-cover">
        <div class="absolute inset-0 -z-10 bg-primary-deep/75" aria-hidden="true"></div>
    @endif
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-20">
        @if ($breadcrumbs)
            <nav aria-label="Breadcrumb" class="mb-4 text-sm text-white/80">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ route('home') }}" class="hover:text-white hover:underline">Home</a></li>
                    @foreach ($breadcrumbs as $label => $url)
                        <li aria-hidden="true">/</li>
                        <li>
                            @if ($loop->last)
                                <span aria-current="page">{{ $label }}</span>
                            @else
                                <a href="{{ $url }}" class="hover:text-white hover:underline">{{ $label }}</a>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif
        <h1 class="text-[1.75rem] font-bold leading-tight md:text-4xl">{{ $title }}</h1>
        @if ($lede)
            <p class="mt-3 max-w-2xl text-lg text-white/90">{{ $lede }}</p>
        @endif
    </div>
</section>
