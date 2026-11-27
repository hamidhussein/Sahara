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
    <dl class="mx-auto grid max-w-7xl grid-cols-1 gap-px bg-line sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as [$number, $label])
            <div class="flex flex-col-reverse bg-surface px-6 py-8">
                <dt class="mt-1 text-ink-muted">{{ $label }}</dt>
                <dd class="text-4xl font-bold text-primary">{{ $number }}</dd>
            </div>
        @endforeach
    </dl>
</section>
