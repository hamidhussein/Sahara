@props(['project', 'headingLevel' => 'h3'])
<article {{ $attributes->class(['group relative flex flex-col overflow-hidden rounded-card border border-line bg-surface']) }}>
    @if ($photo = $project->imageUrl('cover', 'card'))
        <x-photo :src="$photo" :alt="$project->name" class="aspect-[3/2] w-full" />
    @else
        {{-- No photo yet: a slim accent instead of a big empty placeholder. --}}
        <div class="h-1.5 bg-primary" aria-hidden="true"></div>
    @endif
    <div class="flex flex-1 flex-col p-5" @if ($project->language->isRtl()) lang="ur" dir="rtl" @endif>
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <span class="rounded-full px-2.5 py-0.5 font-medium {{ $project->status->tagClasses() }}">{{ $project->status->getLabel() }}</span>
            <span class="text-ink-muted">{{ $project->program->name }}</span>
        </div>
        <{{ $headingLevel }} class="mt-3 text-lg font-semibold leading-snug text-ink">
            <a href="{{ route('projects.show', $project) }}" class="after:absolute after:inset-0 group-hover:text-primary">{{ $project->name }}</a>
        </{{ $headingLevel }}>
        @if ($project->place())
            <p class="mt-1 text-ink-muted">{{ $project->place() }}</p>
        @endif
        @if ($project->people_served)
            <p class="mt-auto pt-4 font-semibold text-primary">
                {{ number_format($project->people_served) }} {{ strtolower($project->people_served_label ?: 'people served') }}
            </p>
        @endif
    </div>
</article>
