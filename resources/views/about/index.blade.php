{{--
    About: story → journey → vision and mission → what we do → values →
    how we are organized and why a foundation → Sahara Complex → working
    together → counters. Rewritten from the foundation's November 2026 copy.
--}}
@php
    $timeline = [
        ['2006', 'The beginning', 'People from District Nagar living in Gilgit set up Syedabad Sahara Welfare Organization (SSWO) to help neighbours in hard times.'],
        ['2013', 'Formal registration', 'Registered under the Voluntary Social Welfare Agencies Ordinance 1961 (Reg. No. '.$site->registration_number.'), giving our welfare work a formal structure.'],
        ['2025', 'Stronger community services', 'Our office was renovated on 1 May. When water to Danyor and Sultanabad was cut off, the "Pani Har Ghar Tak" campaign delivered water door to door for 28 days.'],
        ['2026', 'Wider humanitarian work', 'Scholarships, Ramadan food assistance, a free eye camp for 3,000 patients, new water filtration plants and winter relief reached more families than ever.'],
        ['2026', 'A new chapter', 'We became Subka Sahara Welfare Foundation and bought a building for the Sahara Complex. Registration with the SECP is in process.'],
    ];

    $mission = [
        'Stand with deserving people, vulnerable families and orphan children.',
        'Open doors to quality education.',
        'Bring healthcare and medical help to underserved communities.',
        'Make safe, clean drinking water available to every village we can reach.',
        'Provide food, winter relief and emergency help to families in need.',
        'Encourage young people to volunteer and lead change in their communities.',
        'Build strong partnerships with NGOs, welfare organizations, foundations and philanthropists.',
        'Work transparently and accountably, for development that lasts.',
    ];

    $work = [
        ['Healthcare and medical help', 'Free medical and eye camps, help for patients who cannot afford treatment, our ambulance service and community health work.', route('programs.show', 'health')],
        ['Education and scholarships', 'Scholarships and study support so that deserving and orphan students can stay in education.', route('programs.show', 'education')],
        ['Orphan and child welfare', 'Support for orphan children and the institutions that care for them, especially with schooling and daily needs.', null],
        ['Clean water', 'Community water filtration plants and other water work, focused on villages where safe water is hardest to find.', route('programs.show', 'clean-water')],
        ['Food security', 'Rations and food assistance for struggling households, especially in hard times and important seasons like Ramadan.', route('programs.show', 'social-services')],
        ['Winter relief', 'Blankets, winter packages and essential supplies to help families through Gilgit-Baltistan’s harsh winters.', route('programs.show', 'social-services')],
        ['Community engagement', 'Awareness campaigns and social projects with elders, youth, volunteers and grassroots organizations.', route('programs.show', 'youth-and-community')],
        ['Humanitarian partnerships', 'Planning, local implementation, field coordination, monitoring and reporting for partners’ projects across Gilgit-Baltistan.', route('partner')],
    ];

    $values = [
        ['Compassion', 'We meet people in need with kindness, empathy and respect.'],
        ['Service to humanity', 'We serve everyone without discrimination, and put human dignity first.'],
        ['Integrity and transparency', 'We are honest, careful with every rupee, and accountable for what we do.'],
        ['Community participation', 'Lasting change comes when communities, volunteers and local leaders take part.'],
        ['Collaboration', 'Working with others lets our help reach further.'],
        ['Equality and inclusion', 'Help reaches deserving people whatever their background, color, race, language, religion or political belief.'],
        ['Sustainability', 'We meet today’s needs while building for the long term.'],
    ];

    $structure = [
        ['General Body', 'Registered members from District Nagar living in Gilgit.'],
        ['Executive Body', 'Volunteers who run the organization.'],
        ['Advisory Council', 'Experts who guide our policies.'],
    ];

    $complex = [
        'A low-cost pathology laboratory (first priority)',
        'A pharmacy and OPD',
        'A guest house for patients’ families from far-off areas',
        'A blood donation and awareness center',
        'An IT and vocational training center for youth and women',
        'A counselling, microfinance guidance and conflict resolution center',
        'A conference hall',
        'Our own central office',
    ];

    $h2 = 'text-[1.375rem] font-bold leading-snug text-primary md:text-[1.75rem]';
@endphp
<x-layouts.app title="About Us" description="Subka Sahara Welfare Foundation began in 2006 as Syedabad Sahara Welfare Organization: twenty years of volunteer-led healthcare, education, clean water and relief across Gilgit-Baltistan.">
    <x-site.page-header title="About Us" lede="Twenty years of compassion and service in Gilgit-Baltistan." :breadcrumbs="['About' => null]" />

    {{-- Our story --}}
    <section class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-5 lg:gap-16 lg:px-8 lg:py-20" aria-labelledby="story">
        <div class="lg:col-span-3">
            <h2 id="story" class="{{ $h2 }}">Our story</h2>
            <div class="mt-5 max-w-[68ch] space-y-4 text-lg leading-[1.7]">
                <p>It started in 2006, with a group of people from District Nagar living in Gilgit who wanted to help neighbours going through hard times. They called it Syedabad Sahara Welfare Organization (SSWO). In 2013 it was registered under the Voluntary Social Welfare Agencies Ordinance 1961. It has always been non-profit, non-political and run by volunteers.</p>
                <p>What began as help for one community has grown into welfare work across Gilgit-Baltistan: healthcare, education, clean drinking water, food, winter relief, care for orphans and community development. We support deserving families and orphan students, hold medical camps, install water filtration plants and step in when emergencies strike.</p>
                <p>We rarely work alone. Local communities, welfare organizations, NGOs, philanthropists, overseas Pakistanis and individual donors work with us. Depending on the project, we manage it from start to finish, carry out work our partners fund, or coordinate and supervise in the field.</p>
                <p>Our strength is our roots: close ties to the communities we serve, dedicated volunteers, an understanding of local needs, and a habit of acting together.</p>
                <p>In 2026 we took a new name, Subka Sahara Welfare Foundation, and began building the capacity and partnerships to reach even more communities across the region.</p>
            </div>
        </div>
        <figure class="lg:col-span-2">
            <img src="{{ asset('images/hero-danyor-water.jpg') }}" srcset="{{ \App\Settings\HomeSettings::builtInSrcset() }}" sizes="(min-width: 1024px) 40vw, 100vw" alt="SSWF volunteers delivering clean drinking water in Danyor" loading="lazy" class="aspect-[4/5] w-full rounded-card object-cover">
            <figcaption class="mt-2 text-ink-muted">Volunteers delivering water door to door in Danyor, 2025.</figcaption>
        </figure>
    </section>

    {{-- Our journey: a real sequence, so it reads as a timeline --}}
    <section class="border-y border-line bg-surface" aria-labelledby="journey">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <h2 id="journey" class="{{ $h2 }}">Our journey</h2>
            <ol class="mt-8 max-w-3xl border-s-2 border-primary-tint">
                @foreach ($timeline as [$year, $title, $event])
                    <li class="relative mb-8 ps-8 last:mb-0">
                        <span class="absolute -start-[9px] top-1.5 size-4 rounded-full border-4 border-surface bg-primary" aria-hidden="true"></span>
                        <p class="font-bold text-primary">{{ $year }} · {{ $title }}</p>
                        <p class="mt-1 text-lg text-ink">{{ $event }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Vision and mission --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-20" aria-label="Vision and mission">
        <div class="grid gap-6 lg:grid-cols-5">
            <div class="rounded-card bg-primary p-6 text-white lg:col-span-2 lg:p-8">
                <h2 class="text-xl font-bold">Our vision</h2>
                <p class="mt-3 text-lg text-white/90">To seek the pleasure of Allah by following the guidance of the Holy Quran, and to help build a prosperous, compassionate and self-reliant society where everyone can grow, contribute and live with dignity.</p>
                <p class="mt-3 text-white/80">We picture communities where basic needs are met, education is valued, healthcare is within reach, and people work together for development that lasts.</p>
            </div>
            <div class="rounded-card border border-line bg-surface p-6 lg:col-span-3 lg:p-8">
                <h2 class="text-xl font-bold text-primary">Our mission</h2>
                <p class="mt-3 text-lg">To serve humanity without discrimination, through lasting welfare work, humanitarian help and development led by communities themselves. We aim to:</p>
                <ul class="mt-4 grid gap-x-6 gap-y-2 sm:grid-cols-2">
                    @foreach ($mission as $aim)
                        <li class="flex gap-2">
                            <svg class="mt-1 size-5 shrink-0 text-primary" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.6l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                            <span>{{ $aim }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- What we do --}}
    <section class="border-t border-line bg-surface" aria-labelledby="what-we-do">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <h2 id="what-we-do" class="{{ $h2 }}">What we do</h2>
            <ul class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($work as $i => [$title, $text, $link])
                    <li class="flex flex-col rounded-card border border-line bg-canvas p-5">
                        <span class="text-sm font-semibold text-ink-muted" aria-hidden="true">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="mt-1 text-lg font-semibold leading-snug text-ink">{{ $title }}</h3>
                        <p class="mt-2 grow text-ink-muted">{{ $text }}</p>
                        @if ($link)
                            <a href="{{ $link }}" class="mt-2 inline-flex min-h-11 items-center self-start font-medium text-water-dark underline underline-offset-2 hover:text-primary-deep">Learn more<span class="sr-only"> about {{ strtolower($title) }}</span></a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Values --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-20" aria-labelledby="values">
        <h2 id="values" class="{{ $h2 }}">Our values</h2>
        <dl class="mt-6 grid gap-x-12 gap-y-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($values as [$name, $text])
                <div class="border-s-4 border-primary ps-4">
                    <dt class="text-lg font-semibold text-ink">{{ $name }}</dt>
                    <dd class="mt-1 text-ink-muted">{{ $text }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- How we are organized, and why a foundation --}}
    <section class="border-t border-line bg-surface" aria-labelledby="organized">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 py-12 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-16">
            <div>
                <h2 id="organized" class="{{ $h2 }} scroll-mt-28">How we are organized</h2>
                <dl class="mt-6 space-y-4">
                    @foreach ($structure as [$body, $who])
                        <div class="rounded-card border border-line p-4">
                            <dt class="font-semibold text-ink">{{ $body }}</dt>
                            <dd class="mt-1 text-ink-muted">{{ $who }}</dd>
                        </div>
                    @endforeach
                </dl>
                <a href="{{ route('leadership') }}" class="btn-outline mt-6 text-primary hover:bg-primary-tint">Meet our leadership</a>
            </div>
            <div>
                <h2 class="{{ $h2 }}">Why we became a foundation</h2>
                <p class="mt-5 text-lg leading-[1.7]">For years, SSWO served far beyond its means, but it was registered only within District Gilgit. To work across Gilgit-Baltistan, partner formally with national and international organizations and raise funds at a larger scale, we are registering as a foundation with the SECP and have taken a new name.</p>
                <p class="mt-4 text-lg font-semibold text-primary">The commitment is the same; the reach is wider.</p>
            </div>
        </div>
    </section>

    {{-- Sahara Complex --}}
    <section id="sahara-complex" class="scroll-mt-28 bg-primary-deep text-white" aria-labelledby="complex">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-20">
            <div>
                <h2 id="complex" class="text-[1.375rem] font-bold leading-snug md:text-[1.75rem]">Our next big step: the Sahara Complex</h2>
                <p class="mt-5 text-lg text-white/90">We have bought a building in Gilgit with a foundation approved for five floors; it now has an underground hall and three rented shops. It will be run on a no-profit, no-loss basis.</p>
                <a href="{{ route('donate', ['cause' => 'sahara-complex']) }}" class="btn-donate mt-8 text-lg">Help build the Sahara Complex</a>
            </div>
            <div>
                <h3 class="font-semibold text-white">It will house</h3>
                <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach ($complex as $item)
                        <li class="rounded-button bg-white/10 px-4 py-3">{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- Working together --}}
    <section class="mx-auto max-w-3xl px-4 py-12 text-center sm:px-6 lg:py-20" aria-labelledby="together">
        <h2 id="together" class="{{ $h2 }}">Working together for a better tomorrow</h2>
        <p class="mt-4 text-lg leading-[1.7]">Real change is a shared responsibility. Every gift, every partnership and every hour a volunteer gives can change someone’s life, whether it puts a child through school, brings a doctor to a village, delivers clean water or keeps a family warm in winter.</p>
        <p class="mt-3 text-lg leading-[1.7] text-ink-muted">We welcome organizations, foundations, donors and individuals who share our hope for a healthier, better-educated and more prosperous Gilgit-Baltistan.</p>
        <p class="mt-6 text-xl font-bold text-primary">Together, we can be everyone’s Sahara.</p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ route('donate') }}" class="btn-donate">Donate</a>
            <a href="{{ route('partner') }}" class="btn-primary">Partner with us</a>
            <a href="{{ route('get-involved') }}#volunteer" class="btn-outline text-primary hover:bg-primary-tint">Volunteer</a>
        </div>
    </section>

    <x-impact-counters />
</x-layouts.app>
