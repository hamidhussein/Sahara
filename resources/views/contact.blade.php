{{--
    Contact (plan 1.1): details and form side by side. Instead of an embedded
    map (no confirmed coordinates), the address opens in Google Maps.
--}}
@php
    $mapUrl = 'https://www.google.com/maps/search/?api=1&query='.rawurlencode('Hussainia Complex, Khazana Road, Gilgit');
    $rows = array_filter([
        'Phone' => [$site->office_phone, $site->telLink($site->office_phone)],
        'Ambulance' => $site->ambulance_phone ? [$site->ambulance_phone, $site->telLink($site->ambulance_phone)] : null,
        'WhatsApp' => $site->whatsappLink() ? [$site->whatsapp_number, $site->whatsappLink()] : null,
        'Email' => [$site->email, 'mailto:'.$site->email],
    ]);
@endphp
<x-layouts.app title="Contact Us" description="Contact Subka Sahara Welfare Foundation in Gilgit: office address, phone, email and a message form.">
    <x-site.page-header title="Contact Us" lede="We would love to hear from you." :breadcrumbs="['Contact' => null]" />

    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-5 lg:gap-16 lg:px-8 lg:py-16">
        <section class="lg:col-span-2" aria-labelledby="details">
            <h2 id="details" class="text-[1.375rem] font-bold text-primary md:text-[1.75rem]">Our office</h2>
            <address class="mt-4 text-lg not-italic">{{ $site->address }}</address>
            <a href="{{ $mapUrl }}" rel="noopener" class="btn-outline mt-4 text-primary hover:bg-primary-tint">
                <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z"/></svg>
                Open in Google Maps
            </a>

            <dl class="mt-8 divide-y divide-line rounded-card border border-line bg-surface">
                @foreach ($rows as $label => [$text, $href])
                    <div class="flex flex-wrap items-center justify-between gap-x-4 px-4 py-3">
                        <dt class="text-ink-muted">{{ $label }}</dt>
                        <dd><a href="{{ $href }}" class="inline-flex min-h-11 items-center break-all font-semibold text-water-dark underline underline-offset-2" @if ($label === 'WhatsApp') rel="noopener" @endif>{{ $text }}</a></dd>
                    </div>
                @endforeach
                @if ($site->office_hours)
                    <div class="flex flex-wrap items-center justify-between gap-x-4 px-4 py-3">
                        <dt class="text-ink-muted">Office hours</dt>
                        <dd class="font-medium">{{ $site->office_hours }}</dd>
                    </div>
                @endif
                <div class="flex flex-wrap items-center justify-between gap-x-4 px-4 py-3">
                    <dt class="text-ink-muted">Emergency service</dt>
                    <dd class="font-medium">24 hours a day, 7 days a week</dd>
                </div>
            </dl>

            @if ($site->socialLinks())
                <h3 class="mt-8 font-semibold text-ink">Follow us</h3>
                <ul class="mt-2 flex flex-wrap gap-3">
                    @foreach ($site->socialLinks() as $label => $url)
                        <li><a href="{{ $url }}" rel="noopener" class="btn-outline text-primary hover:bg-primary-tint">{{ $label }}</a></li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="lg:col-span-3" aria-labelledby="write">
            <div class="rounded-card border border-line bg-surface p-6 sm:p-8">
                <h2 id="write" class="text-[1.375rem] font-bold text-primary md:text-[1.75rem]">Send us a message</h2>
                <p class="mt-2 text-ink-muted">For an emergency or the ambulance, call us instead.</p>
                <div class="mt-6"><livewire:contact-form /></div>
            </div>
        </section>
    </div>
</x-layouts.app>
