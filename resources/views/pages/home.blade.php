{{--
    Home page, plan 1.1: 1 Hero, 2 Impact counters, 3 Current appeals with
    "What your gift can do" examples, 4 Our programs, 5 Projects led by one
    featured project, 8 News, 10 Get involved, 9 Partners strip. Still to come:
    6 A beneficiary story (needs a quote given with permission), which should
    take the featured-project spot.
--}}
@php
    $home ??= app(\App\Settings\HomeSettings::class);

    // Structured data so search engines understand who SSWF is (plan 1.4, SEO).
    $organization = [
        '@context' => 'https://schema.org',
        '@type' => 'NGO',
        'name' => config('sswf.name'),
        'alternateName' => ['SSWF', 'Syedabad Sahara Welfare Organization'],
        'url' => route('home'),
        'foundingDate' => (string) config('sswf.founded', 2006),
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
{{-- The page ends with its own Get involved call, so the shared navy Donate band would repeat it. --}}
<x-layouts.app :donateBand="false">
    <x-slot:head>
        <script type="application/ld+json">@json($organization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)</script>
    </x-slot:head>

    {{-- 1. Hero --}}
    <section class="relative isolate overflow-hidden bg-primary-deep text-white">
        <img src="{{ $home->heroImageUrl() }}"
             @if ($srcset = $home->heroSrcset()) srcset="{{ $srcset }}" sizes="100vw" @endif
             alt="{{ $home->hero_image_alt }}"
             fetchpriority="high"
             decoding="async"
             class="absolute inset-0 -z-20 size-full object-cover object-center">
        {{-- Lighter on the right (desktop) and at the top (phones) so the people in the photo stay visible; darkest behind the text. --}}
        <div aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-b from-primary-deep/55 via-primary-deep/75 to-primary-deep/90 lg:bg-gradient-to-r lg:from-primary-deep/92 lg:via-primary-deep/60 lg:to-primary-deep/10"></div>
        <div aria-hidden="true" class="absolute inset-0 -z-10 bg-gradient-to-t from-primary-deep/45 via-transparent to-transparent"></div>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
            <div class="max-w-2xl">
                <p class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 text-sm font-medium text-white/95 backdrop-blur-xs">
                    <span class="size-2 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                    <span>Serving Gilgit-Baltistan since {{ config('sswf.founded', 2006) }}<span class="hidden sm:inline"> · Reg. No. {{ $site->registration_number }}</span></span>
                </p>
                <h1 class="mt-5 text-[2rem] font-bold leading-[1.12] tracking-tight sm:text-5xl lg:text-[3.25rem]">{{ $home->hero_heading }}</h1>
                <p class="mt-4 max-w-xl text-lg leading-relaxed text-white/90 sm:text-xl">
                    {{ $home->hero_text }}
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="{{ route('donate') }}" class="btn-donate px-6 text-lg">Donate Now</a>
                    <a href="{{ route('get-help') }}" class="btn-outline border-white/80 bg-white/10 px-6 text-lg text-white backdrop-blur-xs hover:border-white hover:bg-white/20">Get Help</a>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. Impact counters --}}
    <x-impact-counters class="border-t-0" />

    {{-- 3. Current appeals (Admin → Causes & appeals). Progress shows only when a goal is set. --}}
    @if ($appeals->isNotEmpty())
        <section class="bg-canvas py-14 sm:py-20" aria-labelledby="appeals-title">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-primary">Active appeals</p>
                        <h2 id="appeals-title" class="mt-1 text-[1.375rem] font-bold text-primary md:text-[1.75rem]">Where your help is needed now</h2>
                        <p class="mt-2 max-w-2xl text-ink-muted">Direct your gift to an urgent community appeal, or choose any cause on our donation page.</p>
                    </div>
                    <a href="{{ route('donate') }}" class="hidden min-h-11 shrink-0 items-center gap-1.5 font-semibold text-water-dark hover:text-primary sm:inline-flex">
                        All ways to give <span aria-hidden="true">→</span>
                    </a>
                </div>

                <ul class="mt-8 grid gap-6 lg:grid-cols-3">
                    @foreach ($appeals as $appeal)
                        @php $percent = $appeal->progressPercent(); @endphp
                        <li class="flex flex-col overflow-hidden rounded-card border border-line bg-surface shadow-xs transition-all duration-200 hover:border-primary/40 hover:shadow-md">
                            @if ($photo = $appeal->imageUrl('cover', 'card'))
                                <img src="{{ $photo }}" alt="" loading="lazy" class="aspect-[3/2] w-full object-cover">
                            @else
                                <div class="h-1.5 bg-accent" aria-hidden="true"></div>
                            @endif
                            <div class="flex flex-1 flex-col p-6">
                                @unless ($photo)
                                    <div class="flex items-center justify-between gap-3">
                                        @if ($appeal->program)
                                            <x-program-icon :slug="$appeal->program->slug" class="size-10" />
                                        @else
                                            <span class="grid size-10 shrink-0 place-items-center rounded-full bg-primary-tint text-primary" aria-hidden="true">
                                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M4 21h16M6 21V7l6-4 6 4v14M10 11h4M12 9v4M10 21v-4h4v4" />
                                                </svg>
                                            </span>
                                        @endif
                                        <span class="rounded-full bg-primary-tint px-3 py-1 text-sm font-medium text-primary">
                                            {{ $appeal->program?->name ?? 'Flagship project' }}
                                        </span>
                                    </div>
                                @endunless
                                <h3 class="{{ $photo ? '' : 'mt-4 ' }}text-xl font-bold leading-snug text-ink">{{ $appeal->appeal_title ?: $appeal->name }}</h3>
                                @if ($appeal->description)
                                    <p class="mt-2.5 leading-relaxed text-ink-muted">{{ $appeal->description }}</p>
                                @endif
                                <div class="mt-auto pt-6">
                                    @if ($percent !== null)
                                        <div class="flex items-baseline justify-between gap-2 text-sm">
                                            <span class="text-ink-muted">Raised <strong class="text-ink">Rs {{ number_format($appeal->raisedAmount()) }}</strong> of Rs {{ number_format($appeal->goal_amount) }}</span>
                                            <span class="font-semibold text-primary tabular-nums">{{ $percent }}%</span>
                                        </div>
                                        <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-line" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent }}" aria-label="{{ $percent }}% of goal raised">
                                            <div class="h-full rounded-full bg-accent" style="width: {{ $percent }}%"></div>
                                        </div>
                                    @endif
                                    <a href="{{ route('donate', ['cause' => $appeal->slug]) }}" class="btn-donate mt-4 w-full">Give to {{ preg_replace(['/^(A|An|The)\s+/i', '/\s+\d{4}([–-]\d{2,4})?$/u'], '', $appeal->appeal_title ?: $appeal->name) }}</a>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

                @include('pages.partials.home-gift-examples')
            </div>
        </section>
    @else
        <section class="bg-canvas py-10" aria-label="What your gift can do">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">@include('pages.partials.home-gift-examples')</div>
        </section>
    @endif

    {{-- 4. Our programs --}}
    <section class="border-y border-line bg-surface py-14 sm:py-20" aria-labelledby="programs-title">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-primary">Our programs</p>
                    <h2 id="programs-title" class="mt-1 text-[1.375rem] font-bold text-primary md:text-[1.75rem]">What we do</h2>
                    <p class="mt-2 max-w-2xl text-ink-muted">Eight core programs serving families across Gilgit-Baltistan without discrimination.</p>
                </div>
                <a href="{{ route('programs.index') }}" class="hidden min-h-11 shrink-0 items-center gap-1.5 font-semibold text-water-dark hover:text-primary sm:inline-flex">
                    See all programs <span aria-hidden="true">→</span>
                </a>
            </div>
            {{-- Phones: compact two-column cards with stacked icon so labels never squish; summaries from tablet width up. --}}
            <ul class="mt-8 grid grid-cols-2 gap-3 sm:gap-6 lg:grid-cols-4">
                @foreach ($programs as $program)
                    <li>
                        <a href="{{ route('programs.show', $program) }}" class="group flex h-full flex-col items-start rounded-card border border-line bg-canvas p-4 transition-all duration-150 hover:border-primary/40 hover:bg-surface hover:shadow-sm sm:p-6">
                            <x-program-icon :slug="$program->slug" class="mb-3 size-10 transition-colors group-hover:bg-primary group-hover:text-white sm:mb-4 sm:size-11" />
                            <h3 class="text-[15px] font-semibold leading-snug text-ink group-hover:text-primary sm:text-lg">{{ $program->name }}</h3>
                            <p class="mt-2 hidden text-sm leading-relaxed text-ink-muted sm:block">{{ $program->summary }}</p>
                            <span class="mt-auto hidden items-center gap-1 pt-4 text-sm font-semibold text-water-dark group-hover:text-primary sm:inline-flex">
                                Explore program <span class="transition-transform duration-150 group-hover:translate-x-0.5" aria-hidden="true">→</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <a href="{{ route('programs.index') }}" class="btn-primary mt-6 w-full sm:hidden">See all programs</a>
        </div>
    </section>

    {{-- 5. Latest projects (marked "Show on the home page" in the admin) --}}
    @if ($projects->isNotEmpty())
        <section class="bg-canvas py-14 sm:py-20" aria-labelledby="projects-title">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-primary">Proof of impact</p>
                        <h2 id="projects-title" class="mt-1 text-[1.375rem] font-bold text-primary md:text-[1.75rem]">See what your support has built</h2>
                        <p class="mt-2 max-w-2xl text-ink-muted">Every water plant, medical camp and ration drive is documented with the communities it reached.</p>
                    </div>
                    <a href="{{ route('projects.index') }}" class="hidden min-h-11 shrink-0 items-center gap-1.5 font-semibold text-water-dark hover:text-primary sm:inline-flex">
                        See all projects <span aria-hidden="true">→</span>
                    </a>
                </div>
                @php
                    // The first project with a photo becomes a large feature: a real place and real people
                    // break up the card grids. Replace with a beneficiary story once one is given with consent.
                    $feature = $projects->first(fn ($project) => $project->imageUrl('cover', 'card'));
                    $rest = $feature ? $projects->reject(fn ($project) => $project->is($feature)) : $projects;
                @endphp
                @if ($feature)
                    <article class="group relative mt-8 grid overflow-hidden rounded-card border border-line bg-surface shadow-xs lg:grid-cols-2">
                        <img src="{{ $feature->imageUrl('cover', 'card') }}" alt="{{ $feature->name }}" loading="lazy" class="aspect-[3/2] size-full object-cover lg:aspect-auto">
                        <div class="flex flex-col justify-center p-6 sm:p-10">
                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                <span class="rounded-full px-2.5 py-0.5 font-medium {{ $feature->status->tagClasses() }}">{{ $feature->status->getLabel() }}</span>
                                <span class="text-ink-muted">{{ $feature->program->name }}{{ $feature->place() ? ' · '.$feature->place() : '' }}</span>
                            </div>
                            <h3 class="mt-4 text-2xl font-bold leading-snug text-ink sm:text-3xl">
                                <a href="{{ route('projects.show', $feature) }}" class="after:absolute after:inset-0 group-hover:text-primary">{{ $feature->name }}</a>
                            </h3>
                            <p class="mt-3 text-lg leading-relaxed text-ink-muted">{{ $feature->summary }}</p>
                            @if ($feature->donor)
                                <p class="mt-4 border-s-4 border-accent ps-4 text-ink">{{ $feature->donor }}</p>
                            @endif
                            <span class="mt-6 inline-flex items-center gap-1.5 font-semibold text-water-dark group-hover:text-primary">Read the full story <span aria-hidden="true">→</span></span>
                        </div>
                    </article>
                @endif
                @if ($rest->isNotEmpty())
                    <ul class="mt-6 grid gap-6 sm:grid-cols-2">
                        @foreach ($rest as $project)
                            <li><x-project-card :project="$project" /></li>
                        @endforeach
                    </ul>
                @endif
                <a href="{{ route('projects.index') }}" class="btn-primary mt-6 w-full sm:hidden">See all projects</a>
            </div>
        </section>
    @endif

    {{-- 7. Giving examples now sit under the appeals (pages/partials/home-gift-examples). --}}

    {{-- 8. News --}}
    @if ($posts->isNotEmpty())
        <section class="bg-canvas py-14 sm:py-20" aria-labelledby="news-title">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-primary">News &amp; updates</p>
                        <h2 id="news-title" class="mt-1 text-[1.375rem] font-bold text-primary md:text-[1.75rem]">Latest from the field</h2>
                    </div>
                    <a href="{{ route('news.index') }}" class="hidden min-h-11 shrink-0 items-center gap-1.5 font-semibold text-water-dark hover:text-primary sm:inline-flex">
                        All news <span aria-hidden="true">→</span>
                    </a>
                </div>
                <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)
                        <li><x-post-card :post="$post" /></li>
                    @endforeach
                </ul>
                <a href="{{ route('news.index') }}" class="btn-primary mt-6 w-full sm:hidden">All news</a>
            </div>
        </section>
    @endif

    {{-- 10. Volunteer call --}}
    <section class="border-t border-line bg-canvas py-14 sm:py-20" aria-labelledby="volunteer-title">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-8 rounded-card border border-primary/15 bg-primary-tint/45 p-6 sm:p-10 lg:grid-cols-12 lg:gap-12 lg:p-12">
                <div class="lg:col-span-6">
                    <p class="text-sm font-semibold text-primary">Get involved</p>
                    <h2 id="volunteer-title" class="mt-1 text-[1.375rem] font-bold text-primary md:text-[1.75rem]">{{ $home->volunteer_heading }}</h2>
                    <p class="mt-3 text-lg leading-relaxed text-ink">
                        {{ $home->volunteer_text }}
                    </p>
                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('get-involved') }}#volunteer" class="btn-primary">Become a Volunteer</a>
                        <a href="{{ route('get-involved') }}#blood-donor" class="btn-outline bg-surface text-primary hover:bg-primary-tint">Register as a Blood Donor</a>
                    </div>
                </div>

                <ul class="grid gap-4 sm:grid-cols-2 lg:col-span-6">
                    <li class="rounded-card border border-line bg-surface p-5 shadow-2xs">
                        <span class="grid size-10 place-items-center rounded-full bg-primary-tint text-primary" aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                        </span>
                        <h3 class="mt-3 text-base font-bold text-ink">Field volunteers</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-ink-muted">Join medical camps, household surveys, ration distributions and emergency response near you.</p>
                    </li>
                    <li class="rounded-card border border-line bg-surface p-5 shadow-2xs">
                        <span class="grid size-10 place-items-center rounded-full bg-primary-tint text-primary" aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3.5c3.2 4.1 6 7.4 6 10.7a6 6 0 0 1-12 0c0-3.3 2.8-6.6 6-10.7Z" />
                                <path d="M12 11v4M10 13h4" />
                            </svg>
                        </span>
                        <h3 class="mt-3 text-base font-bold text-ink">Blood Donor Society</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-ink-muted">Register once and stay on call when a patient in your district urgently needs your blood group.</p>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    {{-- 9. Partners: a slim strip (logos when uploaded, otherwise names); sideways scroll on phones. Details live on Partner With Us. --}}
    @if ($partners->isNotEmpty())
        <section class="border-t border-line bg-surface py-10 sm:py-12" aria-labelledby="partners-title">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1">
                    <h2 id="partners-title" class="text-lg font-bold text-primary">Working together with</h2>
                    <a href="{{ route('partner') }}" class="inline-flex min-h-11 items-center gap-1.5 font-semibold text-water-dark hover:text-primary">Partner with us <span aria-hidden="true">→</span></a>
                </div>
                <ul tabindex="0" aria-label="Partners" class="-mx-4 mt-3 flex snap-x gap-3 overflow-x-auto px-4 pb-2 sm:mx-0 sm:flex-wrap sm:overflow-visible sm:px-0 sm:pb-0">
                    @foreach ($partners as $partner)
                        @php $logo = $partner->imageUrl('logo', 'thumb'); @endphp
                        <li class="shrink-0 snap-start">
                            @if ($partner->url)<a href="{{ $partner->url }}" rel="noopener" class="block hover:text-primary">@endif
                            <span class="flex min-h-11 items-center rounded-full border border-line bg-canvas px-4 py-2 text-sm font-medium whitespace-nowrap text-ink">
                                @if ($logo)
                                    <img src="{{ $logo }}" alt="{{ $partner->name }}" loading="lazy" class="max-h-7 w-auto">
                                @else
                                    {{ $partner->name }}
                                @endif
                            </span>
                            @if ($partner->url)</a>@endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
</x-layouts.app>
