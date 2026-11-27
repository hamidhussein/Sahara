{{--
    About (plan 1.1): story → timeline → vision and mission → values → structure
    → why a foundation → Sahara Complex → counters. Text is the plan's final copy.
    Timeline entry "[Year] First water filtration plants open in Phakar" waits
    for the confirmed year.
--}}
@php
    $timeline = [
        ['2006', 'Syedabad Sahara Welfare Organization founded in Gilgit'],
        ['2013', 'Registered under the Voluntary Social Welfare Agencies Ordinance 1961 (Reg. No. '.$site->registration_number.')'],
        ['2025', 'Office renovated (1 May); "Pani Har Ghar Tak" water emergency serves Danyor and Sultanabad for 28 days'],
        ['2026', 'Free eye camp: 3,000 patients examined, 300+ free operations; 1,067 families reached in Ramadan'],
        ['2026', 'Building purchased for the Sahara Complex; renamed Subka Sahara Welfare Foundation; SECP registration in process'],
    ];
    $values = [
        ['Service without discrimination', 'We help everyone, regardless of color, race, language, religion or political belief.'],
        ['Non-profit and non-political', 'Every service is offered at no profit, with free or reduced-cost help for families in need.'],
        ['Honesty and transparency', 'Every rupee is accounted for, and every project is shown publicly.'],
        ['Volunteer spirit', 'Our work is powered by people who give their time freely, day and night.'],
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
<x-layouts.app title="About Us" description="Subka Sahara Welfare Foundation began in 2006 as Syedabad Sahara Welfare Organization. A non-profit, non-political, volunteer-run organization serving Gilgit-Baltistan.">
    <x-site.page-header title="About Us" lede="Twenty years of standing with Gilgit-Baltistan." :breadcrumbs="['About' => null]" />

    {{-- Our story --}}
    <section class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-5 lg:gap-16 lg:px-8 lg:py-20" aria-labelledby="story">
        <div class="lg:col-span-3">
            <h2 id="story" class="{{ $h2 }}">Our story</h2>
            <div class="mt-5 max-w-[68ch] space-y-4 text-lg leading-[1.7]">
                <p>Subka Sahara Welfare Foundation began in 2006 as Syedabad Sahara Welfare Organization (SSWO), started by people from District Nagar living in Gilgit who wanted to help their neighbours in hard times. In 2013 it was registered under the Voluntary Social Welfare Agencies Ordinance 1961. It is a non-profit, non-political organization run by volunteers.</p>
                <p>Over twenty years, SSWO has grown into one of the most active welfare networks in Gilgit-Baltistan. Today we run three ambulances, nine clean water plants and free medical, eye and circumcision camps. We give Ramadan rations and winter packages to families in five districts, and scholarships and interest-free loans to students. At every Muharram, Chehlum and Milad procession in Gilgit, our medical teams and water stalls are there to serve.</p>
                <p>We do this with the support of our members, local donors, overseas Gilgit-Baltistanis and partner organizations, and through the dedication of our volunteers.</p>
            </div>
        </div>
        <figure class="lg:col-span-2">
            <img src="{{ asset('images/hero-danyor-water.jpg') }}" alt="SSWF volunteers delivering clean drinking water in Danyor" loading="lazy" class="aspect-[4/5] w-full rounded-card object-cover">
            <figcaption class="mt-2 text-ink-muted">Volunteers delivering water door to door in Danyor, 2025.</figcaption>
        </figure>
    </section>

    {{-- Our journey: a real sequence, so it reads as a timeline --}}
    <section class="border-y border-line bg-surface" aria-labelledby="journey">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <h2 id="journey" class="{{ $h2 }}">Our journey</h2>
            <ol class="mt-8 max-w-3xl border-s-2 border-primary-tint">
                @foreach ($timeline as [$year, $event])
                    <li class="relative mb-8 ps-8 last:mb-0">
                        <span class="absolute -start-[9px] top-1.5 size-4 rounded-full border-4 border-surface bg-primary" aria-hidden="true"></span>
                        <p class="font-bold text-primary">{{ $year }}</p>
                        <p class="mt-1 text-lg text-ink">{{ $event }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Vision, mission, what we work on --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-20" aria-label="Vision and mission">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-card bg-primary p-6 text-white lg:p-8">
                <h2 class="text-xl font-bold">Our vision</h2>
                <p class="mt-3 text-lg text-white/90">To earn the pleasure of Allah by living according to the guidance of the Holy Quran, and to build a prosperous society where human abilities are used for the welfare and all-round development of people.</p>
            </div>
            <div class="rounded-card border border-line bg-surface p-6 lg:p-8">
                <h2 class="text-xl font-bold text-primary">Our mission</h2>
                <p class="mt-3 text-lg">To follow clear and lasting ways that build a sustainable, well-developed society, and to create an environment where people can grow their skills and make good use of the region’s natural resources.</p>
            </div>
            <div class="rounded-card border border-line bg-surface p-6 lg:p-8">
                <h2 class="text-xl font-bold text-primary">What we work on</h2>
                <ul class="mt-3 flex flex-wrap gap-2">
                    @foreach (['Health', 'Education', 'Social Services', 'Ambulance Service', 'Clean Water'] as $area)
                        <li class="rounded-full bg-primary-tint px-3 py-1 font-medium text-primary-dark">{{ $area }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <h2 class="{{ $h2 }} mt-16">Our values</h2>
        <dl class="mt-6 grid gap-x-12 gap-y-6 md:grid-cols-2">
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

    <x-impact-counters class="border-t-0" />
</x-layouts.app>
