{{-- Sticky bottom bar on phones: Donate · Call Ambulance · WhatsApp (plan 0.2).
     On the Donate pages the Donate button would point at the page itself, so it is left out. --}}
@php
    $callNumber = $site->ambulance_phone ?: $site->office_phone;
    $callLabel = $site->ambulance_phone ? 'Ambulance' : 'Call us';
    $whatsapp = $site->whatsappLink();
    $showDonate = ! request()->routeIs('donate', 'donate.*', 'payments.*');
    $columns = 1 + (int) $showDonate + (int) (bool) $whatsapp;
@endphp
<nav class="fixed inset-x-0 bottom-0 z-40 grid border-t border-line bg-surface lg:hidden {{ ['grid-cols-1', 'grid-cols-2', 'grid-cols-3'][$columns - 1] }}" aria-label="Quick actions">
    @if ($showDonate)
        <a href="{{ route('donate') }}" class="flex h-16 flex-col items-center justify-center gap-0.5 bg-accent text-sm font-semibold text-ink">
            <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 3 4.5 6.8 4.5c2 0 3.6 1.1 5.2 3 1.6-1.9 3.2-3 5.2-3 3.8 0 5.9 3.9 4.4 7.3C19.5 16.4 12 21 12 21Z"/></svg>
            Donate
        </a>
    @endif
    <a href="{{ $site->telLink($callNumber) }}" class="flex h-16 flex-col items-center justify-center gap-0.5 text-sm font-semibold {{ $site->ambulance_phone ? 'text-emergency' : 'text-primary' }}">
        <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1L6.6 10.8Z"/></svg>
        {{ $callLabel }}
    </a>
    @if ($whatsapp)
        <a href="{{ $whatsapp }}" rel="noopener" class="flex h-16 flex-col items-center justify-center gap-0.5 text-sm font-semibold text-primary">
            <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.1-4.5-3.9-4.7-4.1-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .5l-.4.6-.3.3c-.1.2-.3.3-.1.6.2.3.8 1.3 1.6 2 1.1 1 2 1.3 2.3 1.4.3.1.4.1.6-.1l.8-1c.2-.3.4-.2.6-.1l1.9.9c.3.1.4.2.5.3.1.2.1.7-.1 1.2Z"/></svg>
            WhatsApp
        </a>
    @endif
</nav>
