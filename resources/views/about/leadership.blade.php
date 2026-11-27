{{--
    Leadership (plan 1.1): chairman's message → team by section.
    The message appears only once the approved text is in Admin → Settings.
--}}
<x-layouts.app title="Our Leadership" description="The patrons, executive body and advisers who lead Subka Sahara Welfare Foundation.">
    <x-site.page-header title="Our Leadership" lede="The people who carry the responsibility."
        :breadcrumbs="['About' => route('about'), 'Leadership' => null]" />

    @if ($site->chairman_message)
        @php $chairman = $groups->flatten()->first(fn ($m) => $m->role === 'Chairman'); @endphp
        <section class="border-b border-line bg-surface" aria-labelledby="message">
            <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
                <h2 id="message" class="sr-only">Chairman’s message</h2>
                <figure>
                    <blockquote class="text-xl leading-relaxed text-ink md:text-2xl">
                        <p>“{{ $site->chairman_message }}”</p>
                    </blockquote>
                    <figcaption class="mt-6 flex items-center gap-4">
                        @if ($chairman)
                            <x-team-avatar :member="$chairman" class="size-14 text-lg" />
                        @endif
                        <span>
                            <span class="block font-semibold text-ink">{{ $chairman?->name ?? 'Chairman' }}</span>
                            <span class="block text-ink-muted">Chairman, Subka Sahara Welfare Foundation</span>
                        </span>
                    </figcaption>
                </figure>
            </div>
        </section>
    @endif

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        @forelse ($groups as $label => $members)
            <section class="{{ $loop->first ? '' : 'mt-14' }}" aria-labelledby="group-{{ $loop->index }}">
                <h2 id="group-{{ $loop->index }}" class="text-[1.375rem] font-bold text-primary md:text-[1.75rem]">{{ $label }}</h2>
                {{-- Phones: compact rows; from tablet width: centred cards. --}}
                <ul class="mt-6 grid gap-3 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
                    @foreach ($members as $member)
                        <li class="flex items-center gap-4 rounded-card border border-line bg-surface p-4 sm:flex-col sm:p-6 sm:text-center">
                            <x-team-avatar :member="$member" class="size-16 text-lg sm:size-28 sm:text-2xl" />
                            <div>
                                <h3 class="text-lg font-semibold leading-snug text-ink sm:mt-4">{{ $member->name }}</h3>
                                <p class="text-primary">{{ $member->role }}</p>
                                @if ($member->bio)
                                    <p class="mt-2 text-ink-muted sm:mt-3">{{ $member->bio }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p class="text-lg text-ink-muted">Our leadership team will be listed here soon.</p>
        @endforelse

        <p class="mt-14 max-w-3xl text-ink-muted">
            Our Executive Body of volunteers runs the organization, and an Advisory Council of experts guides our policies.
            <a href="{{ route('about') }}#organized" class="font-medium text-water-dark underline underline-offset-2">How we are organized</a>
        </p>
    </div>
</x-layouts.app>
