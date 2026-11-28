{{--
    "What your gift can do": the example amounts from Admin → Settings → Home page,
    shown as a slim line of links instead of a second row of orange buttons.
--}}
@php
    $examples = collect($home->tiers)->values()->map(fn ($tier) => [
        'amount' => filled($tier['amount'] ?? null) ? 'Rs '.number_format((int) $tier['amount']) : 'Any amount',
        'text' => $tier['text'],
        'url' => route('donate', array_filter([
            'cause' => $tier['cause'] ?? null,
            'amount' => filled($tier['amount'] ?? null) ? (int) $tier['amount'] : null,
        ])),
    ]);
@endphp
@if ($examples->isNotEmpty())
    <div class="mt-8 rounded-card border border-line bg-surface p-5 sm:p-6">
        <h3 class="text-sm font-semibold text-ink-muted">What your gift can do</h3>
        <ul class="mt-3 grid gap-x-8 gap-y-2 md:grid-cols-3">
            @foreach ($examples as $example)
                <li>
                    <a href="{{ $example['url'] }}" class="group inline-flex min-h-11 items-baseline gap-2 py-1 text-ink hover:text-primary">
                        <strong class="shrink-0 text-lg font-bold text-primary tabular-nums">{{ $example['amount'] }}</strong>
                        <span class="underline decoration-line underline-offset-4 group-hover:decoration-primary">{{ $example['text'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
