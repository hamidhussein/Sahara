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
            'Volunteer' => route('get-involved'),
            'Become a Blood Donor' => route('get-involved'),
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
                        <li><a href="{{ $url }}" class="inline-flex min-h-11 items-center hover:text-white hover:underline">{{ $label }}</a></li>
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
                <ul class="mt-4 flex flex-wrap gap-x-4 gap-y-1">
                    @foreach ($site->socialLinks() as $label => $url)
                        <li><a href="{{ $url }}" rel="noopener" class="inline-flex min-h-11 items-center font-medium text-white hover:underline">{{ $label }}</a></li>
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
