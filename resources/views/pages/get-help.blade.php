{{-- Get Help (plan 2.1): emergency panel → help cards → one form → success. --}}
@php $ambulance = $site->ambulance_phone ?: $site->office_phone; @endphp
<x-layouts.app title="Get Help" description="Ask Subka Sahara Welfare Foundation for help: blood, medical support, hospital essentials, rations, education support or a wheelchair. In an emergency, call our ambulance.">
    <x-site.page-header title="Get Help" lede="You are not alone. Tell us what you need." :breadcrumbs="['Get Help' => null]" />

    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        {{-- Emergency panel --}}
        <section class="rounded-card border-s-8 border-emergency bg-surface p-6 shadow-sm md:p-8" aria-labelledby="emergency">
            <h2 id="emergency" class="text-[1.375rem] font-bold text-emergency md:text-[1.75rem]">In an emergency, call now.</h2>
            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <a href="{{ $site->telLink($ambulance) }}" class="btn bg-emergency text-lg text-white hover:bg-emergency/90">
                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.15a1.5 1.5 0 0 1 1.46 1.14l.6 2.42a1.5 1.5 0 0 1-.6 1.6l-.9.6a11 11 0 0 0 5.03 5.03l.6-.9a1.5 1.5 0 0 1 1.6-.6l2.42.6A1.5 1.5 0 0 1 18 13.35v1.15a1.5 1.5 0 0 1-1.5 1.5H15C8.37 16 3 10.63 3 4V3.5Z"/></svg>
                    {{ $site->ambulance_phone ? 'Ambulance' : 'Call us' }}: {{ $ambulance }}
                </a>
                @if ($whatsapp = $site->whatsappLink())
                    <a href="{{ $whatsapp }}" class="btn-outline text-lg text-ink hover:bg-primary-tint">WhatsApp: {{ $site->whatsapp_number }}</a>
                @endif
                @if ($site->ambulance_phone)
                    <a href="{{ $site->telLink($site->office_phone) }}" class="btn-outline text-lg text-ink hover:bg-primary-tint">Office: {{ $site->office_phone }}</a>
                @endif
            </div>
            <p class="mt-5 max-w-[68ch] text-ink-muted">Our ambulance is free within Gilgit city. We also take patients to Islamabad and other districts, and carry the deceased home, at nominal charges.</p>
        </section>

        {{-- What happens next. No promised response time until the Foundation confirms one. --}}
        <section class="mt-10" aria-labelledby="next-steps">
            <h2 id="next-steps" class="text-xl font-bold text-primary">What happens after you ask</h2>
            <ol class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['We read your request', 'Our services team sees it straight away. Blood and emergency requests are handled first.'],
                    ['We call you', 'To understand your situation and what is needed. Keep your phone nearby.'],
                    ['We check the need', 'We may ask for a CNIC, prescription, hospital slip or school letter, and local volunteers or elders may visit.'],
                    ['We tell you how we can help', 'If we can help, we explain what we will provide and when. If not, we tell you honestly and suggest where else to go.'],
                ] as $i => [$title, $text])
                    <li class="rounded-card border border-line bg-surface p-4">
                        <span class="text-sm font-semibold text-ink-muted">{{ $i + 1 }}</span>
                        <h3 class="mt-1 font-semibold text-ink">{{ $title }}</h3>
                        <p class="mt-1 text-sm text-ink-muted">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        <div class="mt-14">
            <livewire:help-request-form />
        </div>
    </div>
</x-layouts.app>
