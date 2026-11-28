{{-- Impact counters (plan 1.1), numbers from Admin → Settings → Impact numbers. --}}
@php
    $impact = app(\App\Settings\ImpactSettings::class);
    $stats = [
        [$impact->yearsServing().' years', 'serving Gilgit-Baltistan, since '.$impact->founded_year],
        [$impact->water_plants, 'water filtration plants built to WHO standards'],
        [number_format($impact->patients_examined), 'patients examined and '.$impact->eye_operations.'+ free eye operations in '.$impact->report_year],
        [number_format($impact->ramadan_families), 'families received Ramadan rations in '.$impact->report_year],
    ];
@endphp
<section aria-label="Our impact" {{ $attributes->merge(['class' => 'border-y border-line bg-surface']) }}>
    <dl class="mx-auto grid max-w-7xl grid-cols-2 gap-px bg-line lg:grid-cols-4">
        @foreach ($stats as [$number, $label])
            <div class="flex flex-col-reverse justify-end bg-surface px-4 py-5 sm:px-6 sm:py-7 lg:py-8">
                <dt class="mt-1.5 text-sm leading-snug text-ink-muted sm:text-base">{{ $label }}</dt>
                <dd class="text-[1.75rem] font-bold leading-none tracking-tight text-primary tabular-nums sm:text-3xl lg:text-4xl">{{ $number }}</dd>
            </div>
        @endforeach
    </dl>
</section>
