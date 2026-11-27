{{-- Get Involved (plan 2.1): intro → four ways to help → volunteer and blood donor forms. --}}
@php
    $h2 = 'text-[1.375rem] font-bold leading-snug text-primary md:text-[1.75rem]';
    $ways = [
        ['Volunteer', 'Join camps, surveys, distributions and emergency response.', '#volunteer', 'Sign up below'],
        ['Become a blood donor', 'Join the Sahara Blood Donor Society and be on call to save a life.', '#blood-donor', 'Register below'],
        ['Partner with us', 'Organizations, companies and government departments: let’s do more together.', route('partner'), 'How we partner'],
        ['Start a fundraiser', 'Raise money from friends, family or your workplace for a project you care about.', route('contact', ['subject' => 'Start a fundraiser']), 'Tell us your idea'],
    ];
@endphp
<x-layouts.app title="Get Involved" description="Volunteer with Subka Sahara Welfare Foundation, register as a blood donor, partner with us or start a fundraiser for Gilgit-Baltistan.">
    <x-site.page-header title="Get Involved" lede="Be someone’s Sahara." :breadcrumbs="['Get Involved' => null]" />

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <p class="max-w-[68ch] text-lg leading-[1.7]">Our work runs on people who give their time. In 2025, our volunteers delivered water door to door in Danyor for 28 days. They serve at every eye camp, ration drive and procession. There is a place for you.</p>

        <ul class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($ways as [$title, $text, $url, $cta])
                <li class="flex flex-col rounded-card border border-line bg-surface p-5">
                    <h2 class="text-lg font-semibold text-ink">{{ $title }}</h2>
                    <p class="mt-2 grow text-ink-muted">{{ $text }}</p>
                    <a href="{{ $url }}" class="mt-4 font-medium text-water-dark underline underline-offset-2 hover:text-primary-deep">{{ $cta }}<span class="sr-only">: {{ strtolower($title) }}</span></a>
                </li>
            @endforeach
        </ul>

        <div class="mt-16 grid gap-8 lg:grid-cols-2">
            <section id="volunteer" class="scroll-mt-28 rounded-card border border-line bg-surface p-5 md:p-8" aria-labelledby="volunteer-title">
                <h2 id="volunteer-title" class="{{ $h2 }}">Join as a volunteer</h2>
                <p class="mt-2 text-ink-muted">Tell us a little about yourself. Our volunteer coordinator will call you.</p>
                <div class="mt-6"><livewire:volunteer-form /></div>
            </section>

            <section id="blood-donor" class="scroll-mt-28 rounded-card border border-line bg-surface p-5 md:p-8" aria-labelledby="donor-title">
                <h2 id="donor-title" class="{{ $h2 }}">Become a blood donor</h2>
                <p class="mt-2 text-ink-muted">Your blood, someone’s life. Register once, and we will contact you only when a patient near you needs your blood group.</p>
                <div class="mt-6"><livewire:blood-donor-form /></div>
            </section>
        </div>
    </div>
</x-layouts.app>
