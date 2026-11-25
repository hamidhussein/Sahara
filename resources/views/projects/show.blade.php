@php
    $urdu = $project->language->isRtl();
    $facts = array_filter([
        'Program' => $project->program->name,
        'Status' => $project->status->getLabel(),
        'Place' => $project->place(),
        'Donor' => $project->donor,
        $project->people_served_label ?: 'People served' => $project->people_served ? number_format($project->people_served) : null,
        'Cost' => $project->cost ? 'Rs '.number_format($project->cost) : null,
        'Started' => $project->started_on?->format('j F Y'),
        $project->status === \App\Enums\ProjectStatus::Completed ? 'Completed' : 'Expected' => $project->completed_on?->format('j F Y'),
    ]);
    $before = $project->imageUrl('before', 'card');
    $after = $project->imageUrl('after', 'card');
    $gallery = $project->getMedia('gallery');
@endphp
<x-layouts.app :title="$project->seo_title ?: $project->name" :description="$project->seo_description ?: $project->summary" :urdu="$urdu">
    <x-site.page-header :title="$project->name" :lede="$project->summary" :image="$project->imageUrl('cover', 'hero')"
        :breadcrumbs="['Projects' => route('projects.index'), $project->name => null]" />

    <div class="mx-auto grid max-w-7xl gap-12 px-4 py-12 sm:px-6 lg:grid-cols-3 lg:px-8 lg:py-16">
        <div class="lg:col-span-2">
            @if ($before || $after)
                <section aria-labelledby="before-after" class="mb-12">
                    <h2 id="before-after" class="text-[1.375rem] font-bold text-primary">Before and after</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach (['Before' => $before, 'After' => $after] as $label => $src)
                            @if ($src)
                                <figure>
                                    <img src="{{ $src }}" alt="{{ $project->name }}, {{ strtolower($label) }}" loading="lazy" class="aspect-[4/3] w-full rounded-card object-cover">
                                    <figcaption class="mt-2 font-medium text-ink-muted">{{ $label }}</figcaption>
                                </figure>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($project->story)
                <section aria-label="Story">
                    <x-rich-text :html="$project->story" :language="$project->language" class="max-w-[700px]" />
                </section>
            @endif

            @if ($project->updates->isNotEmpty())
                <section aria-labelledby="updates" class="mt-12">
                    <h2 id="updates" class="text-[1.375rem] font-bold text-primary">Updates</h2>
                    <ol class="mt-6 border-s-2 border-primary-tint">
                        @foreach ($project->updates as $update)
                            <li class="relative mb-8 ps-6 last:mb-0">
                                <span class="absolute -start-[7px] top-2 size-3 rounded-full bg-primary" aria-hidden="true"></span>
                                <time datetime="{{ $update->happened_on->toDateString() }}" class="text-sm font-medium text-ink-muted">{{ $update->happened_on->format('j F Y') }}</time>
                                <h3 class="mt-1 text-lg font-semibold text-ink">{{ $update->title }}</h3>
                                @if ($update->body)
                                    <p class="mt-1 text-ink-muted">{{ $update->body }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            @if ($gallery->isNotEmpty())
                <section aria-labelledby="photos" class="mt-12">
                    <h2 id="photos" class="text-[1.375rem] font-bold text-primary">Photos</h2>
                    <ul class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($gallery as $photo)
                            <li><a href="{{ $photo->getUrl() }}"><img src="{{ $photo->hasGeneratedConversion('thumb') ? $photo->getUrl('thumb') : $photo->getUrl() }}" alt="{{ $project->name }}, photo {{ $loop->iteration }}" loading="lazy" class="aspect-square w-full rounded-card object-cover"></a></li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <aside class="space-y-6">
            <section aria-labelledby="facts" class="rounded-card border border-line bg-surface p-6">
                <h2 id="facts" class="text-lg font-bold text-primary">Key facts</h2>
                <dl class="mt-4 divide-y divide-line">
                    @foreach ($facts as $label => $value)
                        <div class="py-3 first:pt-0 last:pb-0">
                            <dt class="text-sm text-ink-muted">{{ $label }}</dt>
                            <dd class="font-medium text-ink">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section aria-labelledby="fund" class="rounded-card bg-primary p-6 text-white">
                <h2 id="fund" class="text-lg font-bold">Fund a project like this</h2>
                <p class="mt-2 text-white/90">Your gift to {{ $project->program->name }} helps us bring the next one to another village.</p>
                <a href="{{ route('donate', ['cause' => $project->program->slug]) }}" class="btn-donate mt-4 w-full">Donate to {{ $project->program->name }}</a>
            </section>
        </aside>
    </div>

    @if ($related->isNotEmpty())
        <section class="border-t border-line bg-surface" aria-labelledby="related">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <h2 id="related" class="text-[1.375rem] font-bold text-primary">More {{ $project->program->name }} projects</h2>
                <ul class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <li><x-project-card :project="$item" /></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
</x-layouts.app>
