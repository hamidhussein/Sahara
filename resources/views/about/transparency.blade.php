{{--
    Transparency (plan 1.1): registration → reports → where each rupee goes →
    funding and assets → Zakat and Khums policy → questions.
    Documents and the spending breakdown come from Admin → Transparency.
    The policy's bracketed line about administration costs waits for confirmation.
--}}
@php
    $h2 = 'text-[1.375rem] font-bold leading-snug text-primary md:text-[1.75rem]';
    $breakdown = $spendingReport?->spendingBreakdown() ?? [];
@endphp
<x-layouts.app title="Transparency" description="Registration, annual reports, audited accounts and our Zakat and Khums policy. Donors have the right to know exactly how their money is used.">
    <x-site.page-header title="Transparency" lede="Your trust is our most important asset."
        :breadcrumbs="['About' => route('about'), 'Transparency' => null]" />

    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <p class="max-w-[68ch] text-lg leading-[1.7]">
            We believe donors have the right to know exactly how their money is used. This page holds our registration documents, annual reports and audited accounts, and explains how we handle Zakat and Khums.
        </p>

        {{-- Registration --}}
        <section class="mt-12" aria-labelledby="registration">
            <h2 id="registration" class="{{ $h2 }}">Registration</h2>
            <dl class="mt-6 divide-y divide-line rounded-card border border-line bg-surface">
                <div class="grid gap-1 p-4 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-ink-muted">Registered</dt>
                    <dd class="sm:col-span-2">2013, under the Voluntary Social Welfare Agencies (Registration and Control) Ordinance 1961</dd>
                </div>
                <div class="grid gap-1 p-4 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-ink-muted">Registration number</dt>
                    <dd class="font-semibold sm:col-span-2">{{ $site->registration_number }}</dd>
                </div>
                <div class="grid gap-1 p-4 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-ink-muted">SECP</dt>
                    <dd class="sm:col-span-2">Registration under the Companies Act in process</dd>
                </div>
                <div class="grid gap-1 p-4 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-ink-muted">Bank account</dt>
                    <dd class="sm:col-span-2">{{ $bank->bank_name }}, A/C <span class="font-mono">{{ $bank->account_number }}</span></dd>
                </div>
            </dl>
            @if ($certificates->isNotEmpty())
                <ul class="mt-4 flex flex-wrap gap-3">
                    @foreach ($certificates as $doc)
                        <li><a href="{{ $doc->fileUrl() }}" class="btn-outline text-primary hover:bg-primary-tint" download>Download {{ $doc->title }}</a></li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Reports --}}
        <section class="mt-14" aria-labelledby="reports">
            <h2 id="reports" class="{{ $h2 }}">Reports and accounts</h2>
            @if ($reports->isEmpty())
                <p class="mt-4 rounded-card border border-dashed border-line p-6 text-ink-muted">
                    Our annual reports and audited accounts will be published here. Until then, write to
                    <a href="mailto:{{ $site->email }}" class="font-medium text-water-dark underline underline-offset-2">{{ $site->email }}</a> to request a copy.
                </p>
            @else
                <ul class="mt-6 divide-y divide-line rounded-card border border-line bg-surface">
                    @foreach ($reports as $doc)
                        <li class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-ink">{{ $doc->title }}</p>
                                <p class="text-ink-muted">{{ $doc->type->getLabel() }}{{ $doc->fileSize() ? ' · '.$doc->fileSize() : '' }}</p>
                                @if ($doc->summary)
                                    <p class="mt-1 text-ink-muted">{{ $doc->summary }}</p>
                                @endif
                            </div>
                            <a href="{{ $doc->fileUrl() }}" class="btn-primary shrink-0" download>Download</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Where each rupee goes: shown only when Finance has entered real figures --}}
        @if ($breakdown)
            <section class="mt-14" aria-labelledby="spending">
                <h2 id="spending" class="{{ $h2 }}">Where each rupee goes</h2>
                <p class="mt-2 text-ink-muted">Spending by program in {{ $spendingReport->year }}, from {{ $spendingReport->title }}.</p>
                <table class="mt-6 w-full border-separate border-spacing-y-3">
                    <caption class="sr-only">Spending by program in {{ $spendingReport->year }}</caption>
                    <thead class="sr-only">
                        <tr><th scope="col">Program</th><th scope="col">Share</th><th scope="col">Amount</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($breakdown as $row)
                            <tr>
                                <th scope="row" class="w-1/3 pe-4 text-start font-medium text-ink">{{ $row['label'] }}</th>
                                <td class="w-full">
                                    <div class="h-6 rounded-button bg-primary" style="width: {{ max(2, $row['percent']) }}%" aria-hidden="true"></div>
                                </td>
                                <td class="whitespace-nowrap ps-4 text-end text-ink">
                                    <span class="font-semibold">{{ rtrim(rtrim(number_format($row['percent'], 1), '0'), '.') }}%</span>
                                    <span class="block text-sm text-ink-muted">Rs {{ number_format($row['amount']) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        {{-- Funding and assets --}}
        <div class="mt-14 grid gap-6 md:grid-cols-2">
            <section class="rounded-card border border-line bg-surface p-6" aria-labelledby="funding">
                <h2 id="funding" class="text-xl font-bold text-primary">How we are funded</h2>
                <ul class="mt-4 list-disc space-y-1 ps-5">
                    <li>Monthly fees from registered members</li>
                    <li>Donation boxes</li>
                    <li>General donations</li>
                    <li>Sadaqat, Zakat and Khums</li>
                    <li>Rent from the shops in our building</li>
                </ul>
            </section>
            <section class="rounded-card border border-line bg-surface p-6" aria-labelledby="assets">
                <h2 id="assets" class="text-xl font-bold text-primary">What we own</h2>
                <ul class="mt-4 list-disc space-y-1 ps-5">
                    <li>A 5-marla building (basement hall and three rented shops) for the <a href="{{ route('about') }}#sahara-complex" class="font-medium text-water-dark underline underline-offset-2">Sahara Complex</a></li>
                    <li>3 ambulances</li>
                    <li>Office furniture and equipment</li>
                </ul>
            </section>
        </div>

        {{-- Zakat and Khums policy --}}
        <section class="mt-14" aria-labelledby="policy">
            <h2 id="policy" class="{{ $h2 }}">Our Zakat and Khums policy</h2>
            <ul class="mt-6 space-y-4">
                @foreach ([
                    'Zakat, Khums, Fitrana, Fidya and Kaffarah are each kept in a separate fund and recorded separately.',
                    'Zakat and Fitrana go only to eligible (mustahiq) individuals and families.',
                    'Khums is used only for permitted purposes, under the permission (ijazah) of the Marja’s representative, as that permission directs.',
                    'Beneficiaries are verified through surveys by our volunteers and local elders.',
                ] as $rule)
                    <li class="flex gap-4 rounded-card border border-line bg-surface p-4">
                        <svg class="mt-0.5 size-6 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 5 5L20 7"/></svg>
                        <span class="text-lg">{{ $rule }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Questions --}}
        <section class="mt-14 rounded-card bg-primary p-6 text-white sm:p-8" aria-labelledby="questions">
            <h2 id="questions" class="text-xl font-bold">Questions?</h2>
            <p class="mt-2 text-lg text-white/90">
                Write to <a href="mailto:{{ $site->email }}" class="font-semibold text-white underline underline-offset-2">{{ $site->email }}</a>
                or call <a href="{{ $site->telLink($site->office_phone) }}" class="font-semibold text-white underline underline-offset-2">{{ $site->office_phone }}</a> and we will answer.
            </p>
        </section>
    </div>
</x-layouts.app>
