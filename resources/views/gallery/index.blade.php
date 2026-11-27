<x-layouts.app title="Gallery" description="Photos and videos of Subka Sahara Welfare Foundation at work across Gilgit-Baltistan.">
    <x-site.page-header title="Gallery" lede="Moments of service." :breadcrumbs="['Gallery' => null]" />

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        @if ($albums->isEmpty())
            <p class="rounded-card border border-dashed border-line px-6 py-12 text-center text-lg text-ink">
                Photos are on their way. Follow us on Facebook to see our latest work.
            </p>
        @else
            <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($albums as $album)
                    @php
                        $photos = $album->getMedia('photos')->count();
                        $videos = count($album->youtubeVideos());
                    @endphp
                    <li>
                        <article class="group relative overflow-hidden rounded-card border border-line bg-surface">
                            <x-photo :src="$album->coverUrl()" alt="" class="aspect-[4/3] w-full" />
                            <div class="p-5">
                                <h2 class="text-lg font-semibold text-ink">
                                    <a href="{{ route('gallery.show', $album) }}" class="after:absolute after:inset-0 group-hover:text-primary">{{ $album->title }}</a>
                                </h2>
                                <p class="mt-1 text-ink-muted">
                                    {{ collect([
                                        $photos ? $photos.' '.str('photo')->plural($photos) : null,
                                        $videos ? $videos.' '.str('video')->plural($videos) : null,
                                        $album->taken_on?->format('F Y'),
                                    ])->filter()->implode(' · ') }}
                                </p>
                            </div>
                        </article>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.app>
