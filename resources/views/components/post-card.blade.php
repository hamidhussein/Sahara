@props(['post', 'headingLevel' => 'h3', 'featured' => false])
@php($urdu = $post->language->isRtl())
<article {{ $attributes->class([
    'group relative flex h-full overflow-hidden rounded-card border border-line bg-surface shadow-xs transition-all duration-200 hover:border-primary/40 hover:shadow-md',
    'flex-col lg:flex-row' => $featured,
    'flex-col' => ! $featured,
]) }}>
    <div @class(['overflow-hidden bg-primary-tint/40', 'aspect-[3/2] w-full lg:aspect-auto lg:w-3/5' => $featured, 'aspect-[3/2] w-full' => ! $featured])>
        <x-photo :src="$post->imageUrl('cover', $featured ? 'hero' : 'card')" :alt="$post->title"
                 class="size-full transition-transform duration-300 group-hover:scale-[1.03]" />
    </div>
    <div @class(['flex flex-1 flex-col p-5 sm:p-6', 'lg:p-8 lg:justify-center' => $featured]) @if ($urdu) lang="ur" dir="rtl" @endif>
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-ink-muted" dir="ltr" lang="en">
            <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j F Y') }}</time>
            <span class="font-medium text-primary">{{ $post->category->getLabel() }}</span>
            @if ($urdu)
                <span class="rounded-full bg-primary-tint px-2 text-primary" lang="ur">اردو</span>
            @endif
        </div>
        <{{ $headingLevel }} @class(['mt-2 font-semibold leading-snug text-ink', 'text-xl lg:text-2xl' => $featured, 'text-lg' => ! $featured])>
            <a href="{{ route('news.show', $post) }}" class="after:absolute after:inset-0 group-hover:text-primary">{{ $post->title }}</a>
        </{{ $headingLevel }}>
        <p class="mt-2 leading-relaxed text-ink-muted">{{ $post->excerptText() }}</p>
        <div class="mt-auto flex items-center justify-between pt-4 text-sm font-semibold text-water-dark group-hover:text-primary" dir="ltr" lang="en">
            <span>Read story</span>
            <span class="transition-transform duration-150 group-hover:translate-x-0.5" aria-hidden="true">→</span>
        </div>
    </div>
</article>
