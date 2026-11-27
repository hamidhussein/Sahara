{{--
    Partner With Us: how SSWF works with NGOs, foundations, overseas Pakistanis
    and individual philanthropists as a local implementing and coordination partner.
--}}
@php
    $h2 = 'text-[1.375rem] font-bold leading-snug text-primary md:text-[1.75rem]';

    $models = [
        ['Complete project management and implementation', 'For selected projects, we take responsibility for the whole process: planning, fund management, procurement, implementation, monitoring and final reporting.'],
        ['Funded projects with local implementation', 'When a partner funds a project directly, we carry it out on the ground: we coordinate with communities, identify beneficiaries, manage field activities and support monitoring.'],
        ['Local coordination and project facilitation', 'For other initiatives, we act as your local coordination partner: we connect you with communities, identify local needs, arrange field activities and supervise the work.'],
    ];

    $areas = [
        ['Clean drinking water', 'Water filtration plants and safer drinking water for underserved villages.', 'clean-water'],
        ['Food security', 'Ration distribution and food assistance for deserving families.', 'social-services'],
        ['Healthcare', 'Medical camps, healthcare assistance and support for vulnerable patients.', 'health'],
        ['Education', 'Scholarships and educational support for deserving and orphan students.', 'education'],
        ['Winter relief', 'Blankets, winter packages and essential supplies.', 'social-services'],
        ['Community development', 'Youth engagement, awareness campaigns and community welfare.', 'youth-and-community'],
    ];

    $reasons = [
        'Local knowledge and understanding of Gilgit-Baltistan’s communities.',
        'Grassroots connections and community mobilization.',
        'Access to local volunteers and organizational networks.',
        'Flexible implementation and coordination arrangements.',
        'Field-level monitoring and project documentation.',
        'A commitment to responsible humanitarian service.',
    ];
@endphp
<x-layouts.app title="Partner With Us" description="Subka Sahara Welfare Foundation works with NGOs, foundations, overseas Pakistanis and philanthropists as a local implementing and coordination partner across Gilgit-Baltistan.">
    <x-site.page-header title="Partner With Us" lede="Connecting donors, organizations and communities for lasting impact."
        :breadcrumbs="['Get Involved' => route('get-involved'), 'Partner With Us' => null]" />

    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <div class="max-w-[68ch] space-y-4 text-lg leading-[1.7]">
            <p>Subka Sahara Welfare Foundation, which began as Syedabad Sahara Welfare Organization, believes meaningful humanitarian work grows stronger through collaboration, shared responsibility and strong local partnerships.</p>
            <p class="text-ink-muted">Alongside our own welfare work, we collaborate with NGOs, welfare organizations, charitable foundations, overseas Pakistanis and individual philanthropists to deliver humanitarian assistance and community development projects across Gilgit-Baltistan. As a local implementing and coordination partner, we shape our support to the needs, objectives and funding arrangements of each initiative.</p>
        </div>

        {{-- Partnership models --}}
        <section class="mt-14" aria-labelledby="models">
            <h2 id="models" class="{{ $h2 }}">How we can work together</h2>
            <ol class="mt-6 grid gap-4 md:grid-cols-3">
                @foreach ($models as $i => [$title, $text])
                    <li class="flex flex-col rounded-card border border-line bg-surface p-6">
                        <span class="grid size-10 place-items-center rounded-full bg-primary-tint font-bold text-primary" aria-hidden="true">{{ $i + 1 }}</span>
                        <h3 class="mt-4 text-lg font-semibold leading-snug text-ink">{{ $title }}</h3>
                        <p class="mt-2 text-ink-muted">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- Areas of work --}}
        <section class="mt-14" aria-labelledby="areas">
            <h2 id="areas" class="{{ $h2 }}">Areas of collaborative work</h2>
            <ul class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($areas as [$title, $text, $program])
                    <li class="rounded-card border border-line bg-surface p-5">
                        <h3 class="font-semibold text-ink">{{ $title }}</h3>
                        <p class="mt-1 text-ink-muted">{{ $text }}</p>
                        <a href="{{ route('programs.show', $program) }}" class="mt-3 inline-block font-medium text-water-dark underline underline-offset-2 hover:text-primary-deep">See our work<span class="sr-only"> in {{ strtolower($title) }}</span></a>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Why partner --}}
        <section class="mt-14 grid gap-8 rounded-card bg-primary-tint p-6 md:grid-cols-[1fr_1.4fr] md:p-10" aria-labelledby="why">
            <div>
                <h2 id="why" class="{{ $h2 }}">Why partner with Sahara?</h2>
                <p class="mt-3 text-ink-muted">Every project we run is documented and open to our partners. See what we have already delivered with our donors.</p>
                <a href="{{ route('projects.index') }}" class="btn-primary mt-5">See our projects</a>
            </div>
            <ul class="space-y-3">
                @foreach ($reasons as $reason)
                    <li class="flex gap-3">
                        <svg class="mt-1 size-5 shrink-0 text-primary" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.6l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                        <span>{{ $reason }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Call to action --}}
        <section class="mt-14 rounded-card bg-primary-deep p-6 text-white md:p-10" aria-labelledby="together">
            <h2 id="together" class="text-[1.375rem] font-bold leading-snug md:text-[1.75rem]">Let’s work together</h2>
            <p class="mt-3 max-w-[60ch] text-white/85">Whether you are an NGO, a charitable foundation, an overseas philanthropist or an individual donor, we welcome partnerships that improve lives in underserved communities. Together, we can turn resources into meaningful action where it is needed most.</p>
            <p class="mt-4 font-semibold">Partner with Sahara. Connect with communities. Create lasting impact.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('contact', ['subject' => 'Partnership enquiry']) }}" class="btn-donate">Start a partnership</a>
                <a href="mailto:{{ $site->email }}?subject={{ rawurlencode('Partnership enquiry') }}" class="btn border border-white text-white hover:bg-white/10">Email {{ $site->email }}</a>
                @if ($whatsapp = $site->whatsappLink())
                    <a href="{{ $whatsapp }}" class="btn border border-white text-white hover:bg-white/10">WhatsApp us</a>
                @endif
            </div>
        </section>
    </div>
</x-layouts.app>
