{{-- A photo, or a calm placeholder until real SSWF photos are uploaded. --}}
@props(['src' => null, 'alt' => '', 'eager' => false])
@if ($src)
    <img src="{{ $src }}" alt="{{ $alt }}" @if (! $eager) loading="lazy" @endif decoding="async"
         {{ $attributes->class(['object-cover']) }}>
@else
    <div {{ $attributes->class(['grid place-items-center bg-primary-tint text-primary/40']) }} aria-hidden="true">
        <svg class="h-1/3 max-h-16 w-auto" viewBox="0 0 64 40" fill="currentColor"><path d="M0 40 20 12l10 13 8-9 26 24Z"/><circle cx="48" cy="9" r="5"/></svg>
    </div>
@endif
