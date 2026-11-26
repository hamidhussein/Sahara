@php
    $urdu = $post->language->isRtl();
    $shareText = rawurlencode($post->title.' '.route('news.show', $post));
    $gallery = $post->getMedia('gallery');
    $articleData = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => \Illuminate\Support\Str::limit($post->title, 110),
        'description' => $post->excerptText(),
        'inLanguage' => $post->language->value,
        'datePublished' => $post->published_at->toIso8601String(),
        'dateModified' => $post->updated_at->toIso8601String(),
        'image' => $post->imageUrl('cover', 'hero'),
        'mainEntityOfPage' => route('news.show', $post),
        'publisher' => ['@type' => 'NGO', 'name' => config('sswf.name'), 'url' => route('home')],
    ]);
@endphp
<x-layouts.app :title="$post->seo_title ?: $post->title" :description="$post->seo_description ?: $post->excerptText()"
    :image="$post->imageUrl('cover', 'hero')" type="article" :urdu="$urdu">
    <x-slot:head>
        <meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
        <script type="application/ld+json">@json($articleData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)</script>
    </x-slot:head>
    <article>
        <header class="mx-auto max-w-[760px] px-4 pt-10 sm:px-6 lg:pt-16">
            <nav aria-label="Breadcrumb" class="mb-6 text-sm text-ink-muted">
                <ol class="flex flex-wrap gap-2">
                    <li><a href="{{ route('home') }}" class="hover:text-primary hover:underline">Home</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('news.index') }}" class="hover:text-primary hover:underline">News</a></li>
                </ol>
            </nav>
            <p class="flex flex-wrap gap-x-3 text-ink-muted">
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j F Y') }}</time>
                <a href="{{ route('news.index', ['category' => $post->category->value]) }}" class="font-medium text-primary hover:underline">{{ $post->category->getLabel() }}</a>
            </p>
            <h1 class="mt-2 text-[1.75rem] font-bold leading-tight text-ink md:text-4xl" @if ($urdu) lang="ur" dir="rtl" @endif>{{ $post->title }}</h1>
        </header>

        @if ($cover = $post->imageUrl('cover', 'hero'))
            <img src="{{ $cover }}" alt="" class="mx-auto mt-8 aspect-[16/9] w-full max-w-5xl object-cover sm:rounded-card">
        @endif

        <div class="mx-auto max-w-[760px] px-4 py-10 sm:px-6">
            <x-rich-text :html="$post->body" :language="$post->language" />

            @if ($gallery->isNotEmpty())
                <ul class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($gallery as $photo)
                        <li><a href="{{ $photo->getUrl() }}"><img src="{{ $photo->hasGeneratedConversion('thumb') ? $photo->getUrl('thumb') : $photo->getUrl() }}" alt="Photo {{ $loop->iteration }}" loading="lazy" class="aspect-square w-full rounded-card object-cover"></a></li>
                    @endforeach
                </ul>
            @endif

            <div class="mt-10 flex flex-wrap items-center gap-3 border-t border-line pt-6">
                <span class="font-medium text-ink">Share:</span>
                <a href="https://wa.me/?text={{ $shareText }}" rel="noopener" class="btn-primary">WhatsApp</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode(route('news.show', $post)) }}" rel="noopener" class="btn-outline text-primary hover:bg-primary-tint">Facebook</a>
            </div>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="border-t border-line bg-surface" aria-labelledby="related">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <h2 id="related" class="text-[1.375rem] font-bold text-primary">More {{ $post->category->getLabel() }} news</h2>
                <ul class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <li><x-post-card :post="$item" /></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
</x-layouts.app>
