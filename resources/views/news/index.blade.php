@php
    $chip = fn (bool $on) => $on
        ? 'inline-flex min-h-11 items-center rounded-full bg-primary px-4 font-medium text-white'
        : 'inline-flex min-h-11 items-center rounded-full border border-line bg-surface px-4 font-medium text-ink hover:border-primary hover:text-primary';
    $link = fn (array $change) => route('news.index', array_filter(array_merge([
        'category' => $category?->value,
        'language' => $language?->value,
    ], $change)));
@endphp
<x-layouts.app title="News" description="Latest news from Subka Sahara Welfare Foundation: water plants, health camps, rations, Qurbani and events across Gilgit-Baltistan.">
    <x-site.page-header title="News" lede="Latest from the field." :breadcrumbs="['News' => null]" />

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <nav aria-label="Filter news" class="space-y-3">
            <ul class="flex flex-wrap gap-2">
                <li><a href="{{ $link(['category' => null]) }}" class="{{ $chip(! $category) }}" @if (! $category) aria-current="true" @endif>All</a></li>
                @foreach (\App\Enums\PostCategory::cases() as $c)
                    <li><a href="{{ $link(['category' => $c->value]) }}" class="{{ $chip($category === $c) }}" @if ($category === $c) aria-current="true" @endif>{{ $c->getLabel() }}</a></li>
                @endforeach
            </ul>
            <ul class="flex flex-wrap items-center gap-2">
                <li class="me-1 font-medium text-ink-muted">Language:</li>
                <li><a href="{{ $link(['language' => null]) }}" class="{{ $chip(! $language) }}">Both</a></li>
                <li><a href="{{ $link(['language' => 'en']) }}" class="{{ $chip($language?->value === 'en') }}">English</a></li>
                <li><a href="{{ $link(['language' => 'ur']) }}" class="{{ $chip($language?->value === 'ur') }}" lang="ur">اردو</a></li>
            </ul>
        </nav>

        @if ($featured)
            <div class="mt-10"><x-post-card :post="$featured" featured headingLevel="h2" /></div>
        @endif

        @if ($posts->isEmpty() && ! $featured)
            <div class="mt-10 rounded-card border border-dashed border-line px-6 py-12 text-center">
                <p class="text-lg text-ink">
                    {{ $category || $language ? 'No news in this category yet.' : 'No news has been posted yet.' }}
                    Follow us on Facebook to hear first.
                </p>
                @if ($category || $language)
                    <a href="{{ route('news.index') }}" class="btn-outline mt-6 text-primary hover:bg-primary-tint">Show all news</a>
                @endif
            </div>
        @else
            <ul class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <li><x-post-card :post="$post" headingLevel="h2" /></li>
                @endforeach
            </ul>
            <div class="mt-8">{{ $posts->links() }}</div>
        @endif
    </section>
</x-layouts.app>
