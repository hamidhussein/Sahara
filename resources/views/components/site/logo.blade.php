{{-- Placeholder wordmark until the official SVG logo arrives (plan 0.3 handoff). --}}
<a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'flex items-center gap-3']) }} aria-label="{{ config('sswf.name') }}, home">
    <span class="grid size-10 shrink-0 place-items-center rounded-button bg-primary font-bold text-white" aria-hidden="true">S</span>
    <span class="leading-tight">
        <span class="block text-base font-bold text-primary">Sab Ka Sahara</span>
        <span class="block text-xs font-medium text-ink-muted">Welfare Foundation</span>
    </span>
</a>
