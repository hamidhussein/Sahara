{{-- Footer: four columns, registration number and social links (plan 1.1). --}}
@php
    $columns = [
        'Quick links' => [
            'About Us' => route('about'),
            'Our Programs' => route('programs.index'),
            'Projects' => route('projects.index'),
            'Transparency' => route('transparency'),
            'News' => route('news.index'),
            'Contact' => route('contact'),
        ],
        'Get involved' => [
            'Donate' => route('donate'),
            'Volunteer' => route('get-involved').'#volunteer',
            'Become a Blood Donor' => route('get-involved').'#blood-donor',
            'Partner With Us' => route('partner'),
        ],
    ];
@endphp
<footer class="bg-primary-deep text-white/85">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8 lg:py-16">
        <div>
            <h2 class="text-lg font-semibold text-white">About</h2>
            <p class="mt-3">
                Subka Sahara Welfare Foundation (SSWF) is a volunteer-driven welfare organization serving people across Gilgit-Baltistan without discrimination of color, race, language, religion or political belief.
            </p>
            <p lang="ur" dir="rtl" class="mt-3 text-lg text-white">ہمارا عزم – انسانیت کی خدمت</p>
        </div>

        @foreach ($columns as $heading => $links)
            <div>
                <h2 class="text-lg font-semibold text-white">{{ $heading }}</h2>
                <ul class="mt-3 space-y-1">
                    @foreach ($links as $label => $url)
                        <li><a href="{{ $url }}" class="inline-flex min-h-11 min-w-11 items-center hover:text-white hover:underline">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endforeach

        <div>
            <h2 class="text-lg font-semibold text-white">Contact</h2>
            <address class="mt-3 space-y-2 not-italic">
                <p>{{ $site->address }}</p>
                <p><a href="{{ $site->telLink($site->office_phone) }}" class="hover:text-white hover:underline">{{ $site->office_phone }}</a></p>
                <p><a href="mailto:{{ $site->email }}" class="break-all hover:text-white hover:underline">{{ $site->email }}</a></p>
            </address>
            @if ($site->socialLinks())
                @php
                    // Simple 24 × 24 glyphs; the name stays readable to screen readers.
                    $socialIcons = [
                        'Facebook' => '<path fill="currentColor" d="M13.5 21v-7.5h2.5l.4-3h-2.9V8.6c0-.9.3-1.5 1.5-1.5h1.5V4.4a20 20 0 0 0-2.2-.1c-2.2 0-3.7 1.3-3.7 3.8v2.4H8v3h2.6V21h2.9Z"/>',
                        'Instagram' => '<rect x="3.5" y="3.5" width="17" height="17" rx="4.5" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3.8" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor"/>',
                        'YouTube' => '<path fill="currentColor" d="M21.6 7.6a2.6 2.6 0 0 0-1.8-1.8C18.2 5.4 12 5.4 12 5.4s-6.2 0-7.8.4A2.6 2.6 0 0 0 2.4 7.6 27 27 0 0 0 2 12a27 27 0 0 0 .4 4.4 2.6 2.6 0 0 0 1.8 1.8c1.6.4 7.8.4 7.8.4s6.2 0 7.8-.4a2.6 2.6 0 0 0 1.8-1.8A27 27 0 0 0 22 12a27 27 0 0 0-.4-4.4ZM10 15V9l5.2 3L10 15Z"/>',
                        'TikTok' => '<path fill="currentColor" d="M16.6 5.8A4.3 4.3 0 0 1 15.5 3h-3.1v12.4a2.6 2.6 0 1 1-1.8-2.5V9.7a5.7 5.7 0 1 0 4.9 5.7V9.1a7.3 7.3 0 0 0 4.3 1.4V7.4a4.3 4.3 0 0 1-3.2-1.6Z"/>',
                    ];
                @endphp
                <ul class="mt-4 flex flex-wrap gap-2">
                    @foreach ($site->socialLinks() as $label => $url)
                        <li>
                            <a href="{{ $url }}" rel="noopener" class="grid size-11 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20" aria-label="{{ $label }}" title="{{ $label }}">
                                <svg class="size-5" viewBox="0 0 24 24" aria-hidden="true">{!! $socialIcons[$label] !!}</svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="border-t border-white/15">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-6 text-sm sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <p>
                © {{ now()->year }} Subka Sahara Welfare Foundation. Serving since 2006.
                Reg. No. {{ $site->registration_number }} under the Voluntary Social Welfare Agencies Ordinance 1961.
            </p>
            <ul class="flex shrink-0 flex-wrap gap-x-4 gap-y-1 whitespace-nowrap">
                <li><a href="{{ route('privacy') }}" class="hover:text-white hover:underline">Privacy Policy</a></li>
                <li><a href="{{ route('refund-policy') }}" class="hover:text-white hover:underline">Refund Policy</a></li>
                <li><a href="{{ route('terms') }}" class="hover:text-white hover:underline">Terms of Use</a></li>
            </ul>
        </div>
    </div>
</footer>
