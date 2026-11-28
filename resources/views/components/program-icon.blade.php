{{-- One simple outline icon per program (24 × 24, stroke), so program cards are easy to scan. Decorative only. --}}
@props(['slug'])
@php
    $paths = [
        // Water drop
        'clean-water' => 'M12 3.5c3.2 4.1 6 7.4 6 10.7a6 6 0 0 1-12 0c0-3.3 2.8-6.6 6-10.7Z M9.5 14.5a2.5 2.5 0 0 0 2.5 2.5',
        // Medical cross in a circle
        'health' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z M12 8v8 M8 12h8',
        // Ambulance
        'ambulance-service' => 'M3 16V8.5A1.5 1.5 0 0 1 4.5 7H14v9 M14 10h3.5l3 3.2V16H14 M7 16.5a1.75 1.75 0 1 0 3.5 0 1.75 1.75 0 0 0-3.5 0 M15.5 16.5a1.75 1.75 0 1 0 3.5 0 1.75 1.75 0 0 0-3.5 0 M8.5 9.5v3 M7 11h3',
        // Open book
        'education' => 'M12 6.5C10.5 5.3 8.3 5 5 5v12.5c3.3 0 5.5.3 7 1.5 1.5-1.2 3.7-1.5 7-1.5V5c-3.3 0-5.5.3-7 1.5Z M12 6.5V19',
        // Hands holding a heart
        'social-services' => 'M12 12.5s-3.5-2.1-3.5-4.6A1.9 1.9 0 0 1 12 6.8a1.9 1.9 0 0 1 3.5 1.1c0 2.5-3.5 4.6-3.5 4.6Z M3 14.5l3.5 3.5h7l4.5-3 M3 14.5 6 13l3 1.5h4',
        // Crescent moon
        'qurbani' => 'M15.5 3.5a8.5 8.5 0 1 0 5 15.4A7 7 0 0 1 15.5 3.5Z',
        // Group of people
        'youth-and-community' => 'M9 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z M3.5 19a5.5 5.5 0 0 1 11 0 M16 11a2.5 2.5 0 1 0 0-5 M17 13.5a4.5 4.5 0 0 1 3.5 4.5',
        // Water cup with steam (sabeel)
        'religious-events' => 'M6 9h12l-1.3 10.2A2 2 0 0 1 14.7 21H9.3a2 2 0 0 1-2-1.8L6 9Z M9.5 6c0-1 1-1.3 1-2.5 M13.5 6c0-1 1-1.3 1-2.5',
    ];
    $path = $paths[$slug] ?? 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z';
@endphp
<span {{ $attributes->class(['grid size-10 shrink-0 place-items-center rounded-full bg-primary-tint text-primary']) }} aria-hidden="true">
    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
        <path d="{{ $path }}"/>
    </svg>
</span>
