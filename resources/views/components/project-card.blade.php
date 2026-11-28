@props(['project', 'headingLevel' => 'h3'])
<article {{ $attributes->class(['group relative flex h-full flex-col overflow-hidden rounded-card border border-line bg-surface shadow-xs transition-all duration-200 hover:border-primary/40 hover:shadow-md']) }}>
    @if ($photo = $project->imageUrl('cover', 'card'))
        <div class="overflow-hidden bg-primary-tint/40">
            <x-photo :src="$photo" :alt="$project->name" class="aspect-[3/2] w-full transition-transform duration-300 group-hover:scale-[1.03]" />
        </div>
    @else
        {{-- No photo yet: a slim accent instead of a big empty placeholder. --}}
        <div class="h-1.5 bg-primary" aria-hidden="true"></div>
    @endif
    <div class="flex flex-1 flex-col p-5 sm:p-6" @if ($project->language->isRtl()) lang="ur" dir="rtl" @endif>
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <span class="rounded-full px-2.5 py-0.5 font-medium {{ $project->status->tagClasses() }}">{{ $project->status->getLabel() }}</span>
            <span class="font-medium text-ink-muted">{{ $project->program->name }}</span>
        </div>
        <{{ $headingLevel }} class="mt-3 text-lg font-semibold leading-snug text-ink">
            <a href="{{ route('projects.show', $project) }}" class="after:absolute after:inset-0 group-hover:text-primary">{{ $project->name }}</a>
        </{{ $headingLevel }}>
        @if ($project->place())
            <p class="mt-1.5 text-sm text-ink-muted">{{ $project->place() }}</p>
        @endif
        <div class="mt-auto flex items-center justify-between gap-3 border-t border-line/70 pt-4 text-sm">
            @if ($project->people_served)
                <p class="font-semibold text-primary tabular-nums">
                    {{ number_format($project->people_served) }} {{ strtolower($project->people_served_label ?: 'people served') }}
                </p>
            @else
                <span class="font-medium text-water-dark group-hover:text-primary">Read project story</span>
            @endif
            <span class="text-water-dark transition-transform duration-150 group-hover:translate-x-0.5 group-hover:text-primary" aria-hidden="true">→</span>
        </div>
    </div>
</article>
