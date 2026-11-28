<x-layouts.app title="Our Programs" description="Eight ways Subka Sahara Welfare Foundation stands with the people of Gilgit-Baltistan: clean water, health, ambulances, education, social services, Qurbani, youth and religious events.">
    <x-site.page-header title="Our Programs" lede="Eight ways we stand with the people of Gilgit-Baltistan." :breadcrumbs="['Programs' => null]" />

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <ul class="grid gap-6 md:grid-cols-2">
            @foreach ($programs as $program)
                <li>
                    <article class="group relative flex h-full flex-col overflow-hidden rounded-card border border-line bg-surface sm:flex-row">
                        @if ($photo = $program->imageUrl('cover', 'card'))
                            <x-photo :src="$photo" :alt="$program->name" class="aspect-[3/2] w-full sm:aspect-auto sm:w-2/5" />
                        @endif
                        <div class="flex flex-1 flex-col p-6" @if ($program->language->isRtl()) lang="ur" dir="rtl" @endif>
                            @unless ($photo)
                                {{-- No photo yet: the program's icon instead of an empty image block. --}}
                                <x-program-icon :slug="$program->slug" class="mb-4 size-12" />
                            @endunless
                            <h2 class="text-[1.375rem] font-bold leading-snug text-primary">
                                <a href="{{ route('programs.show', $program) }}" class="after:absolute after:inset-0 group-hover:underline">{{ $program->name }}</a>
                            </h2>
                            @if ($program->tagline)
                                <p class="mt-1 font-medium text-ink">{{ $program->tagline }}</p>
                            @endif
                            <p class="mt-3 text-ink-muted">{{ $program->summary }}</p>
                        </div>
                    </article>
                </li>
            @endforeach
        </ul>
    </section>
</x-layouts.app>
