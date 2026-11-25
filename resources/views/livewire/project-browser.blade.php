<div>
    <form class="mt-8 grid gap-4 rounded-card border border-line bg-surface p-4 sm:grid-cols-3" wire:submit.prevent aria-label="Filter projects">
        <label class="block">
            <span class="mb-1 block font-medium text-ink">Program</span>
            <select wire:model.live="program" class="min-h-11 w-full rounded-button border border-line bg-surface px-3">
                <option value="">All programs</option>
                @foreach ($programs as $slug => $name)
                    <option value="{{ $slug }}">{{ $name }}</option>
                @endforeach
            </select>
        </label>
        <label class="block">
            <span class="mb-1 block font-medium text-ink">District</span>
            <select wire:model.live="district" class="min-h-11 w-full rounded-button border border-line bg-surface px-3">
                <option value="">All districts</option>
                @foreach ($districts as $name)
                    <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
            </select>
        </label>
        <label class="block">
            <span class="mb-1 block font-medium text-ink">Status</span>
            <select wire:model.live="status" class="min-h-11 w-full rounded-button border border-line bg-surface px-3">
                <option value="">Any status</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s->value }}">{{ $s->getLabel() }}</option>
                @endforeach
            </select>
        </label>
    </form>

    <div class="mt-8" wire:loading.class="opacity-60" aria-live="polite">
        @if ($projects->isEmpty())
            <div class="rounded-card border border-dashed border-line px-6 py-12 text-center">
                <p class="text-lg text-ink">No projects match these filters yet. Try another district, or fund a new project.</p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    @if ($filtered)
                        <button type="button" wire:click="clearFilters" class="btn-outline text-primary hover:bg-primary-tint">Show all projects</button>
                    @endif
                    <a href="{{ route('donate') }}" class="btn-donate">Fund a new project</a>
                </div>
            </div>
        @else
            <p class="sr-only">{{ $projects->total() }} {{ str('project')->plural($projects->total()) }} found.</p>
            <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    <li wire:key="project-{{ $project->id }}"><x-project-card :project="$project" headingLevel="h2" /></li>
                @endforeach
            </ul>
            <div class="mt-8">{{ $projects->links() }}</div>
        @endif
    </div>
</div>
