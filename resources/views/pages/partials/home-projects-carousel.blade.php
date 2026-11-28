{{--
    Completed projects carousel. Native horizontal scroll with snap (swipe on phones),
    Previous/Next buttons, dots and arrow keys.
    Auto-slides every 6 seconds, but pauses while the mouse is over it, while it has
    keyboard focus or a finger on it, and in a background tab; never for visitors who
    ask their device to reduce motion; and the Pause button stops it (WCAG 2.2.2).
--}}
<div class="mt-8" role="region" aria-roledescription="carousel" aria-label="Completed projects"
     x-data="{
         i: 0,
         n: {{ $projects->count() }},
         playing: false,
         held: false,
         timer: null,
         init() {
             this.playing = this.n > 1 && ! window.matchMedia('(prefers-reduced-motion: reduce)').matches;
             this.restart();
         },
         restart() {
             clearInterval(this.timer);
             this.timer = setInterval(() => {
                 if (this.playing && ! this.held && ! document.hidden) this.show(this.i + 1);
             }, 6000);
         },
         show(k) {
             this.i = (k + this.n) % this.n;
             this.$refs.track.scrollTo({ left: this.i * this.$refs.track.clientWidth, behavior: 'smooth' });
         },
         go(k) { this.show(k); this.restart(); },
         toggle() { this.playing = ! this.playing; this.restart(); },
         sync() { this.i = Math.round(this.$refs.track.scrollLeft / this.$refs.track.clientWidth); },
     }"
     @mouseenter="held = true" @mouseleave="held = false"
     @focusin="held = $event.target.matches(':focus-visible')" @focusout="held = false"
     @touchstart.passive="held = true" @touchend.passive="held = false; restart()">
    <ul x-ref="track" tabindex="0" aria-label="Completed projects. Use the arrow keys to move between them."
        @scroll.debounce.120ms="sync()" @keydown.right.prevent="go(i + 1)" @keydown.left.prevent="go(i - 1)"
        class="flex snap-x snap-mandatory overflow-x-auto rounded-card border border-line bg-surface shadow-xs [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        @foreach ($projects as $project)
            @php $photo = $project->imageUrl('cover', 'card'); @endphp
            <li class="group relative grid w-full shrink-0 snap-start lg:grid-cols-2" data-slide aria-label="{{ $loop->iteration }} of {{ $loop->count }}: {{ $project->name }}">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $project->name }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" class="aspect-[3/2] size-full object-cover lg:aspect-auto lg:h-full lg:min-h-96">
                @else
                    {{-- No photo yet: a branded panel with the program's icon and the result. --}}
                    <div class="flex aspect-[3/2] flex-col items-center justify-center gap-4 bg-primary-tint p-8 text-center lg:aspect-auto lg:min-h-96">
                        <x-program-icon :slug="$project->program->slug" class="size-20 bg-surface [&_svg]:size-10" />
                        @if ($project->people_served)
                            <p class="text-3xl font-bold text-primary tabular-nums">{{ number_format($project->people_served) }}
                                <span class="block text-base font-medium text-ink-muted">{{ strtolower($project->people_served_label ?: 'people served') }}</span>
                            </p>
                        @endif
                    </div>
                @endif
                <div class="flex flex-col justify-center p-6 sm:p-10">
                    <div class="flex flex-wrap items-center gap-2 text-sm">
                        <span class="rounded-full px-2.5 py-0.5 font-medium {{ $project->status->tagClasses() }}">{{ $project->status->getLabel() }}</span>
                        <span class="text-ink-muted">{{ $project->program->name }}{{ $project->place() ? ' · '.$project->place() : '' }}</span>
                    </div>
                    <h3 class="mt-4 text-2xl font-bold leading-snug text-ink sm:text-3xl">
                        <a href="{{ route('projects.show', $project) }}" class="after:absolute after:inset-0 group-hover:text-primary">{{ $project->name }}</a>
                    </h3>
                    <p class="mt-3 text-lg leading-relaxed text-ink-muted">{{ $project->summary }}</p>
                    @if ($project->donor)
                        <p class="mt-4 border-s-4 border-accent ps-4 text-ink">{{ $project->donor }}</p>
                    @elseif ($photo && $project->people_served)
                        <p class="mt-4 font-semibold text-primary">{{ number_format($project->people_served) }} {{ strtolower($project->people_served_label ?: 'people served') }}</p>
                    @endif
                    <span class="mt-6 inline-flex items-center gap-1.5 font-semibold text-water-dark group-hover:text-primary">Read the full story <span aria-hidden="true">→</span></span>
                </div>
            </li>
        @endforeach
    </ul>

    @if ($projects->count() > 1)
        <div class="mt-4 flex items-center justify-between gap-4">
            {{-- Dots from tablet width up; phones swipe and use the counter and arrows. --}}
            <div class="hidden flex-wrap sm:flex">
                @foreach ($projects as $project)
                    <button type="button" @click="go({{ $loop->index }})"
                            :aria-current="i === {{ $loop->index }} ? 'true' : 'false'"
                            aria-label="Show project {{ $loop->iteration }}: {{ $project->name }}"
                            class="grid size-11 place-items-center">
                        <span class="block h-2.5 rounded-full transition-all" :class="i === {{ $loop->index }} ? 'w-7 bg-primary' : 'w-2.5 bg-line'"></span>
                    </button>
                @endforeach
            </div>
            <div class="ms-auto flex shrink-0 items-center gap-2">
                {{-- Announced only when paused, so screen readers aren't interrupted every 6 seconds. --}}
                <span class="me-1 text-sm font-medium text-ink-muted tabular-nums" :aria-live="playing ? 'off' : 'polite'"><span x-text="i + 1">1</span> / {{ $projects->count() }}</span>
                <button type="button" @click="toggle()" :aria-label="playing ? 'Pause automatic slides' : 'Play automatic slides'"
                        x-cloak x-show="n > 1"
                        class="grid size-11 place-items-center rounded-full border border-line bg-surface text-primary hover:border-primary">
                    <svg x-show="playing" class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6 4.5A1.5 1.5 0 0 1 7.5 3h.5a1.5 1.5 0 0 1 1.5 1.5v11A1.5 1.5 0 0 1 8 17h-.5A1.5 1.5 0 0 1 6 15.5v-11ZM10.5 4.5A1.5 1.5 0 0 1 12 3h.5A1.5 1.5 0 0 1 14 4.5v11a1.5 1.5 0 0 1-1.5 1.5H12a1.5 1.5 0 0 1-1.5-1.5v-11Z"/></svg>
                    <svg x-show="! playing" class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.3 3.6A1 1 0 0 0 4.8 4.5v11a1 1 0 0 0 1.5.9l9.2-5.5a1 1 0 0 0 0-1.8L6.3 3.6Z"/></svg>
                </button>
                <button type="button" @click="go(i - 1)" aria-label="Previous project" class="grid size-11 place-items-center rounded-full border border-line bg-surface text-primary hover:border-primary">
                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.8 4.2a.75.75 0 0 1 0 1.06L8.06 10l4.74 4.74a.75.75 0 1 1-1.06 1.06l-5.27-5.27a.75.75 0 0 1 0-1.06l5.27-5.27a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
                </button>
                <button type="button" @click="go(i + 1)" aria-label="Next project" class="grid size-11 place-items-center rounded-full border border-line bg-surface text-primary hover:border-primary">
                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.2 15.8a.75.75 0 0 1 0-1.06L11.94 10 7.2 5.26a.75.75 0 1 1 1.06-1.06l5.27 5.27a.75.75 0 0 1 0 1.06l-5.27 5.27a.75.75 0 0 1-1.06 0Z" clip-rule="evenodd"/></svg>
                </button>
            </div>
        </div>
    @endif
</div>
