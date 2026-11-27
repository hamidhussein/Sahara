{{-- Official mark (سہارا) with the name as text, so it stays sharp and readable at header size. --}}
<a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'flex min-h-11 items-center gap-3']) }}>
    <img src="{{ asset('images/brand/mark-320.webp') }}" srcset="{{ asset('images/brand/mark-160.webp') }} 160w, {{ asset('images/brand/mark-320.webp') }} 320w"
         sizes="97px" width="97" height="40" alt="" class="h-10 w-auto shrink-0">
    <span class="leading-tight">
        <span class="block text-base font-bold text-primary">Subka Sahara</span>
        <span class="block text-xs font-medium text-ink-muted"><span class="sr-only"> </span>Welfare Foundation</span>
    </span>
</a>
