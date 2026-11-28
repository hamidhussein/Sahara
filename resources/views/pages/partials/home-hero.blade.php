{{--
    Hero carousel (Admin → Settings → Home page → Hero slides). The photo cross-fades and the
    message changes; the Donate Now and Get Help buttons stay on every slide. Slim indicators
    at the bottom right show and switch the slide: the active one is longer and, while the
    slides move on their own, fills up to show the time until the next one.
    Auto-advance runs every few seconds (the admin's "Seconds per slide"), also while the
    mouse is over it. Picking a slide by click, tap or swipe restarts the countdown. It holds
    only while a keyboard user is on the indicators, stops in background tabs, and never runs
    for visitors who ask for reduced motion.
    All messages share one grid cell, so the hero keeps the height of the longest message.
--}}
@php
    $slides = $home->heroSlides();
    $count = count($slides);
    $interval = max(4, min(15, (int) $home->hero_interval)) * 1000;
@endphp
<section class="relative isolate overflow-hidden bg-primary-deep text-white"
         role="region" aria-roledescription="carousel" aria-label="Highlights"
         x-data="{
             i: 0,
             n: {{ $count }},
             interval: {{ $interval }},
             autoplay: false,
             focus: false,
             hidden: false,
             cycle: 0,
             timer: null,
             startX: null,
             get running() { return this.autoplay && ! this.focus && ! this.hidden; },
             init() {
                 this.autoplay = {{ $home->hero_autoplay ? 'true' : 'false' }} && this.n > 1
                     && ! window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                 document.addEventListener('visibilitychange', () => { this.hidden = document.hidden; this.restart(); });
                 this.schedule();
             },
             schedule() {
                 clearTimeout(this.timer);
                 if (this.running) this.timer = setTimeout(() => this.show(this.i + 1), this.interval);
             },
             restart() { this.cycle++; this.schedule(); },
             show(k) { this.i = (k + this.n) % this.n; this.restart(); },
             // A click, tap or swipe: go there; the next slide follows after a full slide time.
             choose(k) { this.show(k); },
             swipe(endX) {
                 if (this.startX !== null && Math.abs(endX - this.startX) > 50) this.choose(this.i + (endX < this.startX ? 1 : -1));
                 this.startX = null;
             },
             // The active indicator fills over the slide's time while moving on its own, and is solid otherwise.
             fill(el, index) {
                 this.cycle;
                 const active = this.i === index;
                 const filling = active && this.running;
                 el.style.transition = 'none';
                 el.style.width = active && ! filling ? '100%' : '0%';
                 if (filling) {
                     el.offsetWidth;
                     el.style.transition = 'width ' + this.interval + 'ms linear';
                     el.style.width = '100%';
                 }
             },
         }"
         @focusin="if ($event.target.matches(':focus-visible')) { focus = true; schedule(); }"
         @focusout="if (! $el.contains($event.relatedTarget)) { focus = false; restart(); }"
         @touchstart.passive="startX = $event.touches[0].clientX"
         @touchend.passive="swipe($event.changedTouches[0].clientX)">

    {{-- Background photos, stacked; the active one fades in. --}}
    @foreach ($slides as $index => $slide)
        <img src="{{ $slide['src'] }}"
             @if ($slide['srcset']) srcset="{{ $slide['srcset'] }}" sizes="100vw" @endif
             alt="{{ $slide['alt'] }}"
             @if ($index === 0) fetchpriority="high" @else loading="lazy" fetchpriority="low" @endif
             decoding="async"
             @if ($index > 0) aria-hidden="true" @endif
             class="absolute inset-0 -z-20 size-full object-cover object-center transition-opacity duration-1000 ease-in-out motion-reduce:transition-none {{ $index === 0 ? 'opacity-100' : 'opacity-0' }}"
             :class="{ 'opacity-100': i === {{ $index }}, 'opacity-0': i !== {{ $index }} }"
             :aria-hidden="i === {{ $index }} ? 'false' : 'true'">
    @endforeach
    {{-- Lighter on the right (desktop) and at the top (phones) so the people in the photo stay visible; darkest behind the text. --}}
    <div aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-b from-primary-deep/55 via-primary-deep/75 to-primary-deep/90 lg:bg-gradient-to-r lg:from-primary-deep/92 lg:via-primary-deep/60 lg:to-primary-deep/10"></div>
    <div aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-t from-primary-deep/45 via-transparent to-transparent"></div>

    {{-- Extra room at the bottom on phones keeps the indicators clear of the stacked buttons. --}}
    <div @class(['mx-auto max-w-7xl px-4 pt-12 sm:px-6 sm:py-20 lg:px-8 lg:py-24', 'pb-20' => $count > 1, 'pb-12' => $count <= 1])>
        <div class="max-w-2xl">
            {{-- One fixed page heading for search engines and screen readers; each slide's message is a sub-heading. --}}
            <h1 class="sr-only">{{ config('sswf.name') }}</h1>
            <div class="grid" :aria-live="running ? 'off' : 'polite'">
                @foreach ($slides as $index => $slide)
                    {{-- The outgoing message fades first; the incoming one follows a moment later. --}}
                    <div class="col-start-1 row-start-1 transition-[opacity,visibility,translate] duration-500 ease-out motion-reduce:transition-none {{ $index === 0 ? '' : 'invisible translate-y-2 opacity-0' }}"
                         :class="{ 'visible translate-y-0 opacity-100 delay-200': i === {{ $index }}, 'invisible translate-y-2 opacity-0 delay-0': i !== {{ $index }} }"
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
        </div>
    </div>

    @if ($count > 1)
        {{-- Slide indicators, bottom right, lined up with the page's content edge. --}}
        <div class="pointer-events-none absolute inset-x-0 bottom-0" data-hero-indicators>
            <div class="mx-auto flex max-w-7xl justify-end px-2.5 pb-3 sm:px-4.5 sm:pb-5 lg:px-6.5 lg:pb-6">
                <div class="pointer-events-auto flex items-center" role="group" aria-label="Choose a slide">
                    @foreach ($slides as $index => $slide)
                        <button type="button" @click="choose({{ $index }})"
                                :aria-current="i === {{ $index }} ? 'true' : 'false'"
                                aria-label="Show slide {{ $index + 1 }} of {{ $count }}: {{ $slide['heading'] }}"
                                class="group grid h-11 min-w-11 place-items-center px-1.5">
                            {{-- A soft shadow, not a background, keeps the lines visible on bright photos. --}}
                            <span class="relative block h-1 overflow-hidden rounded-full shadow-[0_1px_3px_rgb(0_0_0/0.45)] transition-[width,background-color] duration-300 ease-out motion-reduce:transition-none {{ $index === 0 ? 'w-10 bg-white/40' : 'w-5 bg-white/60' }}"
                                  :class="{ 'w-10 bg-white/40': i === {{ $index }}, 'w-5 bg-white/60 group-hover:bg-white/90': i !== {{ $index }} }">
                                <span class="absolute inset-y-0 start-0 rounded-full bg-white {{ $index === 0 ? 'w-full' : 'w-0' }}"
                                      x-effect="fill($el, {{ $index }})"></span>
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</section>
