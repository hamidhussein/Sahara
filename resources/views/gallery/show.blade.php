@php
    $photos = $album->getMedia('photos')->values()->map(fn ($m, $i) => [
        'thumb' => $m->hasGeneratedConversion('card') ? $m->getUrl('card') : $m->getUrl(),
        'full' => $m->hasGeneratedConversion('hero') ? $m->getUrl('hero') : $m->getUrl(),
        'alt' => $album->title.', photo '.($i + 1),
    ]);
    $videos = $album->youtubeVideos();
@endphp
<x-layouts.app :title="$album->title" :description="$album->description ?: 'Photos from '.$album->title.', Sab Ka Sahara Welfare Foundation.'">
    <x-site.page-header :title="$album->title" :lede="$album->description" :breadcrumbs="['Gallery' => route('gallery'), $album->title => null]" />

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16"
         x-data="{
            photos: @js($photos),
            current: 0,
            video: null,
            open(i) { this.current = i; this.$refs.lightbox.showModal() },
            step(d) { this.current = (this.current + d + this.photos.length) % this.photos.length },
            play(v) { this.video = v; this.$refs.player.showModal() },
         }">

        @if ($videos)
            <section aria-labelledby="videos-title" class="mb-12">
                <h2 id="videos-title" class="text-[1.375rem] font-bold text-primary">Videos</h2>
                <ul class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($videos as $video)
                        <li>
                            <button type="button" @click="play(@js($video))" class="group relative block w-full overflow-hidden rounded-card text-start">
                                <img src="https://i.ytimg.com/vi/{{ $video['id'] }}/hqdefault.jpg" alt="" loading="lazy" class="aspect-video w-full object-cover">
                                <span class="absolute inset-0 grid place-items-center bg-ink/30 group-hover:bg-ink/40" aria-hidden="true">
                                    <span class="grid size-16 place-items-center rounded-full bg-white/95 text-primary">
                                        <svg class="ms-1 size-7" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                    </span>
                                </span>
                                <span class="block bg-surface p-3 font-medium text-ink">Play video: {{ $video['title'] }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($photos->isNotEmpty())
            <section aria-labelledby="photos-title">
                <h2 id="photos-title" class="{{ $videos ? '' : 'sr-only' }} text-[1.375rem] font-bold text-primary">Photos</h2>
                <ul class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($photos as $i => $photo)
                        <li>
                            <button type="button" @click="open({{ $i }})" class="block w-full overflow-hidden rounded-card">
                                <img src="{{ $photo['thumb'] }}" alt="{{ $photo['alt'] }}" loading="lazy" class="aspect-square w-full object-cover transition-transform duration-200 hover:scale-[1.03] motion-reduce:transition-none motion-reduce:hover:scale-100">
                            </button>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- Lightbox: the native dialog handles focus and Escape --}}
            <dialog x-ref="lightbox" class="m-auto max-h-none max-w-none bg-transparent p-0 backdrop:bg-primary-deep/95"
                    @keydown.arrow-right="step(1)" @keydown.arrow-left="step(-1)" @click.self="$el.close()" aria-label="Photo viewer">
                <figure class="flex h-dvh w-screen flex-col items-center justify-center gap-3 p-4">
                    <img :src="photos[current]?.full" :alt="photos[current]?.alt" class="max-h-[80dvh] max-w-full rounded-card object-contain">
                    <figcaption class="text-white" x-text="`${current + 1} of ${photos.length}`"></figcaption>
                    <div class="flex gap-3">
                        <button type="button" @click="step(-1)" class="btn bg-white/15 text-white hover:bg-white/25" x-show="photos.length > 1">Previous</button>
                        <button type="button" @click="$refs.lightbox.close()" class="btn bg-white text-ink">Close</button>
                        <button type="button" @click="step(1)" class="btn bg-white/15 text-white hover:bg-white/25" x-show="photos.length > 1">Next</button>
                    </div>
                </figure>
            </dialog>
        @endif

        {{-- Video player: loads only when opened, unloads on close (no autoplay on page load) --}}
        <dialog x-ref="player" class="m-auto w-[min(960px,calc(100vw-2rem))] rounded-card bg-ink p-0 backdrop:bg-primary-deep/95"
                @close="video = null" @click.self="$el.close()" aria-label="Video player">
            <template x-if="video">
                <div>
                    <div class="aspect-video">
                        <iframe class="size-full" :src="`https://www.youtube-nocookie.com/embed/${video.id}?autoplay=1&rel=0`" :title="video.title"
                                allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
                    </div>
                    <div class="flex items-center justify-between gap-4 p-3">
                        <p class="text-white" x-text="video.title"></p>
                        <button type="button" @click="$refs.player.close()" class="btn bg-white text-ink">Close</button>
                    </div>
                </div>
            </template>
        </dialog>

        <a href="{{ route('gallery') }}" class="btn-outline mt-12 text-primary hover:bg-primary-tint">All albums</a>
    </div>
</x-layouts.app>
