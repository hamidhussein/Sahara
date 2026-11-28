{{--
    Home page, plan 1.1: 1 Hero, 2 Impact counters, 3 Current appeals with
    "What your gift can do" examples, 4 Our programs, 5 Completed projects
    carousel, 8 News, 10 Get involved, 9 Partners strip. Still to come:
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

    {{-- 1. Hero carousel (pages/partials/home-hero, Admin → Settings → Home page → Hero slides) --}}
    @include('pages.partials.home-hero')

    {{-- 2. Impact counters --}}
    <x-impact-counters class="border-t-0" />

    {{-- 2b. About Subka Sahara --}}
    @php
        $aboutVideoId = $home->aboutVideoId();
        $aboutVideoTitle = trim((string) $home->about_video_title) ?: 'Subka Sahara Welfare Foundation story';
    @endphp
    <section class="border-b border-line bg-surface py-14 sm:py-20 lg:py-24"
             aria-labelledby="home-about-title"
             @if ($aboutVideoId) x-data="{ videoOpen: false, playVideo() { this.videoOpen = true; this.$refs.aboutVideoPlayer.showModal(); } }" @endif>
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-12 lg:gap-14 lg:px-8">
            <div class="lg:col-span-6">
                <div class="relative mx-auto max-w-[32rem] pt-6 sm:pt-8 lg:mx-0">
                    {{-- Photo 1 (top-left) + orange semicircle accent tucked underneath --}}
                    <div class="relative z-0 w-[64%]">
                        <div class="overflow-hidden rounded-card border-[6px] border-surface bg-surface shadow-md">
                            <img src="{{ asset('images/about-eye-camp-960.webp') }}"
                                 srcset="{{ asset('images/about-eye-camp-640.webp') }} 640w, {{ asset('images/about-eye-camp-960.webp') }} 960w"
                                 sizes="(min-width: 1024px) 22vw, 60vw"
                                 alt="Doctor examining a senior patient during an SSWF free eye camp in Nagar"
                                 loading="lazy"
                                 decoding="async"
                                 class="aspect-square w-full object-cover object-center">
                        </div>
                        <div class="absolute -bottom-5 left-[18%] -z-10 h-10 w-20 rounded-b-full bg-accent sm:-bottom-7 sm:h-14 sm:w-28" aria-hidden="true"></div>
                    </div>

                    {{-- Photo 2 (bottom-right overlap) + navy semicircle accent above; doubles as the YouTube story trigger when configured --}}
                    <div class="relative z-10 -mt-28 ml-auto w-[54%] sm:-mt-40">
                        <div class="absolute -top-5 right-3 -z-10 h-10 w-20 rounded-t-full bg-primary sm:-top-7 sm:right-5 sm:h-14 sm:w-28" aria-hidden="true"></div>
                        @if ($aboutVideoId)
                            <button type="button"
                                    @click="playVideo()"
                                    aria-label="Watch our story: {{ $aboutVideoTitle }}"
                                    class="group relative block w-full cursor-pointer overflow-hidden rounded-card border-[6px] border-surface bg-surface text-start shadow-xl transition-shadow duration-200 hover:shadow-2xl">
                                <img src="{{ asset('images/about-water-plant-960.webp') }}"
                                     srcset="{{ asset('images/about-water-plant-640.webp') }} 640w, {{ asset('images/about-water-plant-960.webp') }} 960w"
                                     sizes="(min-width: 1024px) 19vw, 50vw"
                                     alt=""
                                     loading="lazy"
                                     decoding="async"
                                     class="aspect-[4/5] w-full object-cover object-[38%_45%] transition-transform duration-300 group-hover:scale-[1.04] motion-reduce:transition-none motion-reduce:group-hover:scale-100">
                                <span aria-hidden="true" class="absolute inset-0 bg-gradient-to-t from-primary-deep/80 via-primary-deep/20 to-transparent transition-colors duration-200 group-hover:from-primary-deep/90 group-hover:via-primary-deep/40"></span>
                                <span aria-hidden="true" class="absolute inset-0 grid place-items-center">
                                    <span class="rounded-full bg-white/25 p-2 backdrop-blur-xs transition-all duration-200 group-hover:scale-110 group-hover:bg-accent/40 motion-reduce:transition-none motion-reduce:group-hover:scale-100">
                                        <span class="grid size-12 place-items-center rounded-full bg-white text-primary shadow-lg transition-colors duration-200 group-hover:bg-accent group-hover:text-ink sm:size-14">
                                            <svg class="ms-0.5 size-5 sm:size-6" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                        </span>
                                    </span>
                                </span>
                                <span aria-hidden="true" class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-2 px-3 py-2.5 sm:px-4 sm:py-3">
                                    <span class="text-sm font-semibold leading-tight text-white">Watch our story</span>
                                    <span class="hidden size-7 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur-xs transition-colors group-hover:bg-accent group-hover:text-ink sm:inline-flex">
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                    </span>
                                </span>
                            </button>
                        @else
                            <div class="overflow-hidden rounded-card border-[6px] border-surface bg-surface shadow-xl">
                                <img src="{{ asset('images/about-water-plant-960.webp') }}"
                                     srcset="{{ asset('images/about-water-plant-640.webp') }} 640w, {{ asset('images/about-water-plant-960.webp') }} 960w"
                                     sizes="(min-width: 1024px) 19vw, 50vw"
                                     alt="A woman collecting clean drinking water from SSWF's filtration plant in Miachar, Nagar"
                                     loading="lazy"
                                     decoding="async"
                                     class="aspect-[4/5] w-full object-cover object-[38%_45%]">
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6">
                <p class="inline-flex items-center gap-2 text-sm font-semibold text-water-dark">
                    <span class="size-2 rounded-full bg-accent" aria-hidden="true"></span>
                    About Subka Sahara
                </p>
                <h2 id="home-about-title" class="mt-2 text-[1.625rem] font-bold leading-[1.2] text-primary sm:text-3xl lg:text-[2.25rem]">
                    Serving Gilgit-Baltistan with dignity since 2006.
                </h2>
                <p class="mt-4 text-base leading-relaxed text-ink-muted sm:text-lg">
                    Started in 2006 as Syedabad Sahara Welfare Organization by people from District Nagar living in Gilgit, Subka Sahara Welfare Foundation is a non-profit, non-political organization led by volunteers. From clean drinking water and free eye camps to scholarships, winter relief and ambulances, we stand with families in need without discrimination.
                </p>

                <div class="mt-7 grid gap-6 border-t border-line pt-6 sm:grid-cols-2">
                    <div class="flex items-start gap-3.5">
                        <span class="grid size-12 shrink-0 place-items-center rounded-full border-2 border-water/35 bg-primary-tint/50 text-water-dark" aria-hidden="true">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9" />
                                <circle cx="12" cy="12" r="5" />
                                <circle cx="12" cy="12" r="1.5" fill="currentColor" stroke="none" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-ink sm:text-lg">Our Mission</h3>
                            <p class="mt-1 text-sm leading-relaxed text-ink-muted">
                                Serve humanity without discrimination through lasting clean water, healthcare, education and relief led by communities.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <span class="grid size-12 shrink-0 place-items-center rounded-full border-2 border-accent/65 bg-accent/10 text-primary" aria-hidden="true">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 19h18" />
                                <path d="m5 19 5.5-9 3.5 5 2.5-3.5L19 19" />
                                <circle cx="16.5" cy="7" r="2" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-ink sm:text-lg">Our Vision</h3>
                            <p class="mt-1 text-sm leading-relaxed text-ink-muted">
                                Compassionate, self-reliant communities where clean water, education and healthcare are within everyone’s reach.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <a href="{{ route('about') }}" class="btn-primary px-6">Discover More</a>
                </div>
            </div>
        </div>

        @if ($aboutVideoId)
            {{-- Embedded YouTube player: loads only when opened, unloads on close. --}}
            <dialog x-ref="aboutVideoPlayer"
                    class="m-auto w-[min(960px,calc(100vw-2rem))] overflow-hidden rounded-card bg-primary-deep p-0 text-white shadow-2xl backdrop:bg-primary-deep/90"
                    @close="videoOpen = false"
                    @click.self="$el.close()"
                    aria-label="{{ $aboutVideoTitle }}">
                <template x-if="videoOpen">
                    <div>
                        <div class="aspect-video bg-black">
                            <iframe class="size-full"
                                    src="https://www.youtube-nocookie.com/embed/{{ $aboutVideoId }}?autoplay=1&rel=0"
                                    title="{{ $aboutVideoTitle }}"
                                    allow="autoplay; encrypted-media; picture-in-picture"
                                    allowfullscreen></iframe>
                        </div>
                        <div class="flex items-center justify-between gap-4 border-t border-white/10 bg-primary-deep px-4 py-3 sm:px-6">
                            <p class="font-semibold text-white">{{ $aboutVideoTitle }}</p>
                            <button type="button" @click="$refs.aboutVideoPlayer.close()" class="btn bg-white text-ink hover:bg-canvas">Close</button>
                        </div>
                    </div>
                </template>
            </dialog>
        @endif
    </section>

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

    {{-- 5. Completed projects carousel (pages/partials/home-projects-carousel) --}}
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
                @include('pages.partials.home-projects-carousel')
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
