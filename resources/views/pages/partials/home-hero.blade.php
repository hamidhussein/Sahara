{{--
    Hero carousel (Admin → Settings → Home page → Hero slides). The background photo
    cross-fades and the message changes; the Donate Now and Get Help buttons stay put.
    All messages share one grid cell, so the hero keeps the height of the longest and
    the page never jumps. Auto-advance follows the admin setting, pauses on hover,
    keyboard focus, touch and in background tabs, never runs for reduced motion, and
    has a Pause button (WCAG 2.2.2).
--}}
@php
    $slides = $home->heroSlides();
    $count = count($slides);
@endphp
<section class="relative isolate overflow-hidden bg-primary-deep text-white"
         role="region" aria-roledescription="carousel" aria-label="Highlights"
         x-data="{
             i: 0,
             n: {{ $count }},
             playing: false,
             held: false,
             timer: null,
             startX: null,
             init() {
                 this.playing = {{ $home->hero_autoplay ? 'true' : 'false' }} && this.n > 1
                     && ! window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                 this.restart();
             },
             restart() {
                 clearInterval(this.timer);
                 this.timer = setInterval(() => {
                     if (this.playing && ! this.held && ! document.hidden) this.i = (this.i + 1) % this.n;
                 }, {{ max(4, min(15, (int) $home->hero_interval)) * 1000 }});
             },
             go(k) { this.i = (k + this.n) % this.n; this.restart(); },
             toggle() { this.playing = ! this.playing; this.restart(); },
             swipe(endX) {
                 if (this.startX === null) return;
                 const dx = endX - this.startX;
                 if (Math.abs(dx) > 50) this.go(this.i + (dx < 0 ? 1 : -1));
                 this.startX = null;
                 this.held = false;
             },
         }"
         @mouseenter="held = true" @mouseleave="held = false"
         @focusin="held = $event.target.matches(':focus-visible')" @focusout="held = false"
         @touchstart.passive="held = true; startX = $event.touches[0].clientX"
         @touchend.passive="swipe($event.changedTouches[0].clientX)">

    {{-- Background photos, stacked; the active one fades in. --}}
    @foreach ($slides as $index => $slide)
        <img src="{{ $slide['src'] }}"
             @if ($slide['srcset']) srcset="{{ $slide['srcset'] }}" sizes="100vw" @endif
             alt="{{ $slide['alt'] }}"
             @if ($index === 0) fetchpriority="high" @else loading="lazy" fetchpriority="low" @endif
             decoding="async"
             @if ($index > 0) aria-hidden="true" @endif
             class="absolute inset-0 -z-20 size-full object-cover object-center transition-opacity duration-1000 ease-in-out {{ $index === 0 ? 'opacity-100' : 'opacity-0' }}"
             :class="{ 'opacity-100': i === {{ $index }}, 'opacity-0': i !== {{ $index }} }"
             :aria-hidden="i === {{ $index }} ? 'false' : 'true'">
    @endforeach
    {{-- Lighter on the right (desktop) and at the top (phones) so the people in the photo stay visible; darkest behind the text. --}}
    <div aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-b from-primary-deep/55 via-primary-deep/75 to-primary-deep/90 lg:bg-gradient-to-r lg:from-primary-deep/92 lg:via-primary-deep/60 lg:to-primary-deep/10"></div>
    <div aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-t from-primary-deep/45 via-transparent to-transparent"></div>

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
        <div class="max-w-2xl">
            {{-- One fixed page heading for search engines and screen readers; each slide's message is a sub-heading. --}}
            <h1 class="sr-only">{{ config('sswf.name') }}</h1>
            <p class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 text-sm font-medium text-white/95 backdrop-blur-xs">
                <span class="size-2 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                <span>Serving Gilgit-Baltistan since {{ config('sswf.founded', 2006) }}<span class="hidden sm:inline"> · Reg. No. {{ $site->registration_number }}</span></span>
            </p>

            <div class="mt-5 grid" :aria-live="playing ? 'off' : 'polite'">
                @foreach ($slides as $index => $slide)
                    <div class="col-start-1 row-start-1 transition-[opacity,visibility,translate] duration-500 ease-out {{ $index === 0 ? '' : 'invisible translate-y-2 opacity-0' }}"
                         :class="{ 'visible translate-y-0 opacity-100': i === {{ $index }}, 'invisible translate-y-2 opacity-0': i !== {{ $index }} }"
                         @if ($count > 1) aria-roledescription="slide" aria-label="{{ $index + 1 }} of {{ $count }}" role="group" @endif>
                        <h2 class="text-[2rem] font-bold leading-[1.12] tracking-tight sm:text-5xl lg:text-[3.25rem]">{{ $slide['heading'] }}</h2>
                        <p class="mt-4 max-w-xl text-lg leading-relaxed text-white/90 sm:text-xl">{{ $slide['text'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                <a href="{{ route('donate') }}" class="btn-donate px-6 text-lg">Donate Now</a>
                <a href="{{ route('get-help') }}" class="btn-outline border-white/80 bg-white/10 px-6 text-lg text-white backdrop-blur-xs hover:border-white hover:bg-white/20">Get Help</a>
            </div>

            @if ($count > 1)
                <div class="mt-8 flex items-center gap-1">
                    @foreach ($slides as $index => $slide)
                        <button type="button" @click="go({{ $index }})"
                                :aria-current="i === {{ $index }} ? 'true' : 'false'"
                                aria-label="Show message {{ $index + 1 }}: {{ $slide['heading'] }}"
                                class="grid size-11 place-items-center">
                            <span class="block h-1.5 rounded-full transition-all duration-300 {{ $index === 0 ? 'w-8 bg-white' : 'w-4 bg-white/45' }}"
                                  :class="{ 'w-8 bg-white': i === {{ $index }}, 'w-4 bg-white/45': i !== {{ $index }} }"></span>
                        </button>
                    @endforeach
                    <button type="button" @click="toggle()" x-cloak
                            :aria-label="playing ? 'Pause automatic slides' : 'Play automatic slides'"
                            class="ms-2 grid size-11 place-items-center rounded-full border border-white/40 bg-white/10 text-white backdrop-blur-xs hover:bg-white/20">
                        <svg x-show="playing" class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6 4.5A1.5 1.5 0 0 1 7.5 3h.5a1.5 1.5 0 0 1 1.5 1.5v11A1.5 1.5 0 0 1 8 17h-.5A1.5 1.5 0 0 1 6 15.5v-11ZM10.5 4.5A1.5 1.5 0 0 1 12 3h.5A1.5 1.5 0 0 1 14 4.5v11a1.5 1.5 0 0 1-1.5 1.5H12a1.5 1.5 0 0 1-1.5-1.5v-11Z"/></svg>
                        <svg x-show="! playing" class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.3 3.6A1 1 0 0 0 4.8 4.5v11a1 1 0 0 0 1.5.9l9.2-5.5a1 1 0 0 0 0-1.8L6.3 3.6Z"/></svg>
                    </button>
                </div>
            @endif
        </div>
    </div>
</section>
