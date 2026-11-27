{{--
    Program page (plan 1.1): hero → description → this program's projects.
    Still to come: stats, giving amounts (with the donation module) and gallery.
--}}
<x-layouts.app :title="$program->seo_title ?: $program->name" :description="$program->seo_description ?: $program->summary" :image="$program->imageUrl('cover', 'hero')" :urdu="$program->language->isRtl()">
    <section class="bg-primary-deep text-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-2 lg:items-center lg:gap-12 lg:px-8 lg:py-16">
            <div @if ($program->language->isRtl()) lang="ur" dir="rtl" @endif>
                <nav aria-label="Breadcrumb" class="mb-4 text-sm text-white/80" dir="ltr" lang="en">
                    <ol class="flex flex-wrap gap-2">
                        <li><a href="{{ route('home') }}" class="hover:text-white hover:underline">Home</a></li>
                        <li aria-hidden="true">/</li>
                        <li><a href="{{ route('programs.index') }}" class="hover:text-white hover:underline">Programs</a></li>
                        <li aria-hidden="true">/</li>
                        <li aria-current="page">{{ $program->name }}</li>
                    </ol>
                </nav>
                <h1 class="text-[1.75rem] font-bold leading-tight md:text-4xl">{{ $program->name }}</h1>
                @if ($program->tagline)
                    <p class="mt-3 text-xl text-white/90">{{ $program->tagline }}</p>
                @endif
                <a href="{{ route('donate', ['cause' => $program->slug]) }}" class="btn-donate mt-8 text-lg">Donate to this program</a>
            </div>
            @if ($cover = $program->imageUrl('cover', 'hero'))
                <x-photo :src="$cover" :alt="$program->name" eager class="aspect-[3/2] w-full rounded-card" />
            @endif
        </div>
    </section>

    @if ($program->body)
        <section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <x-rich-text :html="$program->body" :language="$program->language" />
        </section>
    @endif

    @if ($projects->isNotEmpty())
        <section class="border-t border-line bg-surface" aria-labelledby="program-projects">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
                <h2 id="program-projects" class="text-[1.375rem] font-bold text-primary md:text-[1.75rem]">{{ $program->name }} projects</h2>
                <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <li><x-project-card :project="$project" /></li>
                    @endforeach
                </ul>
                <a href="{{ route('projects.index', ['program' => $program->slug]) }}" class="btn-primary mt-8">See all {{ $program->name }} projects</a>
            </div>
        </section>
    @endif
</x-layouts.app>
