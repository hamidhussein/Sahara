{{--
    Home page, plan 1.1. Built: 1 Hero, 2 Impact counters, 3 Current appeals,
    4 Our programs, 5 Latest projects, 7 Giving tiers, 8 News, 9 Partners,
    10 Volunteer call. Still to come: 6 A story (needs a beneficiary quote
    given with permission).
--}}
@php
    // Structured data so search engines understand who SSWF is (plan 1.4, SEO).
    $organization = [
        '@context' => 'https://schema.org',
        '@type' => 'NGO',
        'name' => config('sswf.name'),
        'alternateName' => ['SSWF', 'Syedabad Sahara Welfare Organization'],
        'url' => route('home'),
        'foundingDate' => '2006',
        'description' => $site->seo_description,
        'email' => $site->email,
        'telephone' => $site->office_phone,
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'G-18, Hussainia Complex, Khazana Road',
            'addressLocality' => 'Gilgit',
            'addressRegion' => 'Gilgit-Baltistan',
            'addressCountry' => 'PK',
        ],
        'areaServed' => 'Gilgit-Baltistan',
        'sameAs' => array_values($site->socialLinks()),
    ];
@endphp
<x-layouts.app>
    <x-slot:head>
        <script type="application/ld+json">@json($organization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)</script>
    </x-slot:head>

    {{-- 1. Hero --}}
    <section class="relative isolate overflow-hidden bg-primary-deep text-white">
        <img src="{{ asset('images/hero-danyor-water.jpg') }}"
             alt="Subka Sahara volunteers delivering clean drinking water in Danyor"
             fetchpriority="high"
             decoding="async"
             class="absolute inset-0 -z-20 size-full object-cover object-center">
        <div aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-r from-primary-deep/95 via-primary-deep/80 to-primary-deep/45"></div>
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8 lg:py-32">
            <div class="max-w-2xl">
                <h1 class="text-[2rem] font-bold leading-[1.15] sm:text-5xl">Support for everyone who has none.</h1>
                <p class="mt-5 text-lg text-white/90 sm:text-xl">
                    From clean water in Nagar to free eye surgeries and food for families in need, Subka Sahara Welfare Foundation stands with the people of Gilgit-Baltistan, day and night.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('donate') }}" class="btn-donate text-lg">Donate Now</a>
                    <a href="{{ route('get-help') }}" class="btn-outline text-lg text-white hover:bg-white/10">Get Help</a>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. Impact counters --}}
    <x-impact-counters class="border-t-0" />

    {{-- 3. Current appeals (Admin → Causes & appeals). Progress shows only when a goal is set. --}}
    @if ($appeals->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-16 sm:px-6 lg:px-8 lg:pt-24" aria-labelledby="appeals-title">
            <h2 id="appeals-title" class="text-[1.375rem] font-bold text-primary md:text-[1.75rem]">Where your help is needed now</h2>
            <ul class="mt-8 grid gap-6 lg:grid-cols-3">
                @foreach ($appeals as $appeal)
                    @php $percent = $appeal->progressPercent(); @endphp
                    <li class="flex flex-col overflow-hidden rounded-card border border-line bg-surface">
                        @if ($photo = $appeal->imageUrl('cover', 'card'))
                            <img src="{{ $photo }}" alt="" loading="lazy" class="aspect-[3/2] w-full object-cover">
                        @endif
                        <div class="flex flex-1 flex-col p-6">
                            <h3 class="text-lg font-semibold text-ink">{{ $appeal->appeal_title ?: $appeal->name }}</h3>
                            @if ($appeal->description)
                                <p class="mt-2 text-ink-muted">{{ $appeal->description }}</p>
                            @endif
                            <div class="mt-auto pt-6">
                                @if ($percent !== null)
                                    <div class="h-2.5 overflow-hidden rounded-full bg-line" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent }}" aria-label="{{ $percent }}% of goal raised">
                                        <div class="h-full rounded-full bg-accent" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <p class="mt-2 text-sm text-ink-muted">
                                        Raised <strong class="text-ink">Rs {{ number_format($appeal->raisedAmount()) }}</strong> of Rs {{ number_format($appeal->goal_amount) }}
                                    </p>
                                @endif
                                <a href="{{ route('donate', ['cause' => $appeal->slug]) }}" class="btn-donate mt-4 w-full">Donate</a>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- 4. Our programs --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24" aria-labelledby="programs-title">
        <h2 id="programs-title" class="text-[1.375rem] font-bold text-primary md:text-[1.75rem]">What we do</h2>
        {{-- Phones: a compact two-column list of names; summaries from tablet width up. --}}
        <ul class="mt-8 grid grid-cols-2 gap-3 sm:gap-6 lg:grid-cols-4">
            @foreach ($programs as $program)
                <li>
                    <a href="{{ route('programs.show', $program) }}" class="group flex h-full min-h-16 items-center rounded-card border border-line bg-surface p-4 hover:border-primary sm:block sm:p-6">
                        <h3 class="font-semibold leading-snug text-ink group-hover:text-primary sm:text-lg">{{ $program->name }}</h3>
                        <p class="mt-2 hidden text-ink-muted sm:block">{{ $program->summary }}</p>
                    </a>
                </li>
            @endforeach
        </ul>
        <a href="{{ route('programs.index') }}" class="btn-primary mt-8">See all programs</a>
    </section>

    {{-- 5. Latest projects (marked "Show on the home page" in the admin) --}}
    @if ($projects->isNotEmpty())
        <section class="border-y border-line bg-surface" aria-labelledby="projects-title">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
                <h2 id="projects-title" class="text-[1.375rem] font-bold text-primary md:text-[1.75rem]">See what your support has built</h2>
                <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <li><x-project-card :project="$project" /></li>
                    @endforeach
                </ul>
                <a href="{{ route('projects.index') }}" class="btn-primary mt-8">See all projects</a>
            </div>
        </section>
    @endif

    {{-- 7. How your donation helps. The Rs 15,000 eye surgery tier waits for the confirmed cost. --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24" aria-labelledby="tiers-title">
        <h2 id="tiers-title" class="text-[1.375rem] font-bold text-primary md:text-[1.75rem]">How your donation helps</h2>
        @php
            $tiers = [
                ['Rs 10,000', 'gives one family a Ramadan ration package', ['cause' => 'ramadan-package', 'amount' => 10000]],
                ['Rs 50,000', 'helps a talented student through a year of medical or engineering study (interest-free loan)', ['cause' => 'education', 'amount' => 50000]],
                ['Any amount', 'brings the Sahara Complex, and its low-cost laboratory, a step closer', ['cause' => 'sahara-complex']],
            ];
        @endphp
        <ul class="mt-8 grid gap-6 md:grid-cols-3">
            @foreach ($tiers as [$amount, $text, $query])
                <li class="flex flex-col rounded-card border border-line bg-surface p-6">
                    <p class="text-3xl font-bold text-primary">{{ $amount }}</p>
                    <p class="mt-2 flex-1 text-lg text-ink">{{ $text }}</p>
                    <a href="{{ route('donate', $query) }}" class="btn-donate mt-6">Give {{ $amount === 'Any amount' ? 'any amount' : $amount }}</a>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- 8. News --}}
    @if ($posts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24" aria-labelledby="news-title">
            <h2 id="news-title" class="text-[1.375rem] font-bold text-primary md:text-[1.75rem]">Latest from the field</h2>
            <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <li><x-post-card :post="$post" /></li>
                @endforeach
            </ul>
            <a href="{{ route('news.index') }}" class="btn-primary mt-8">All news</a>
        </section>
    @endif

    {{-- 9. Partners: logos when uploaded, otherwise names --}}
    @if ($partners->isNotEmpty())
        <section class="{{ $posts->isNotEmpty() ? 'border-t border-line' : '' }} bg-canvas" aria-labelledby="partners-title">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <h2 id="partners-title" class="text-[1.375rem] font-bold text-primary md:text-[1.75rem]">Working together</h2>
                <ul class="mt-8 grid grid-cols-2 gap-4 md:grid-cols-4">
                    @foreach ($partners as $partner)
                        <li class="flex min-h-20 items-center justify-center rounded-card border border-line bg-surface p-4 text-center font-medium text-ink-muted">
                            @php $logo = $partner->imageUrl("logo", "thumb"); @endphp
                            @if ($partner->url)<a href="{{ $partner->url }}" rel="noopener" class="hover:text-primary">@endif
                                @if ($logo)
                                    <img src="{{ $logo }}" alt="{{ $partner->name }}" loading="lazy" class="max-h-12 w-auto">
                                @else
                                    {{ $partner->name }}
                                @endif
                            @if ($partner->url)</a>@endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- 10. Volunteer call --}}
    <section class="border-t border-line bg-surface" aria-labelledby="volunteer-title">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="max-w-2xl">
                <h2 id="volunteer-title" class="text-[1.375rem] font-bold text-primary md:text-[1.75rem]">Be someone’s Sahara.</h2>
                <p class="mt-3 text-lg text-ink">
                    Our volunteers carried water to thirsty homes in Danyor for 28 days and served at every camp and procession. Join them.
                </p>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('get-involved') }}#volunteer" class="btn-primary">Become a Volunteer</a>
                    <a href="{{ route('get-involved') }}#blood-donor" class="btn-outline text-primary hover:bg-primary-tint">Register as a Blood Donor</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
