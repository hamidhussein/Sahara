@props(['post', 'headingLevel' => 'h3', 'featured' => false])
@php($urdu = $post->language->isRtl())
<article {{ $attributes->class([
    'group relative flex overflow-hidden rounded-card border border-line bg-surface',
    'flex-col lg:flex-row' => $featured,
    'flex-col' => ! $featured,
]) }}>
    <x-photo :src="$post->imageUrl('cover', $featured ? 'hero' : 'card')" :alt="$post->title"
             :class="$featured ? 'aspect-[3/2] w-full lg:aspect-auto lg:w-3/5' : 'aspect-[3/2] w-full'" />
    <div @class(['flex flex-1 flex-col p-5', 'lg:p-8 lg:justify-center' => $featured]) @if ($urdu) lang="ur" dir="rtl" @endif>
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-ink-muted" dir="ltr" lang="en">
            <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j F Y') }}</time>
            <span>{{ $post->category->getLabel() }}</span>
            @if ($urdu)
                <span class="rounded-full bg-primary-tint px-2 text-primary" lang="ur">اردو</span>
            @endif
        </div>
        <{{ $headingLevel }} @class(['mt-2 font-semibold leading-snug text-ink', 'text-xl lg:text-2xl' => $featured, 'text-lg' => ! $featured])>
            <a href="{{ route('news.show', $post) }}" class="after:absolute after:inset-0 group-hover:text-primary">{{ $post->title }}</a>
        </{{ $headingLevel }}>
        <p class="mt-2 text-ink-muted">{{ $post->excerptText() }}</p>
    </div>
</article>
