{{-- Ambulance and WhatsApp numbers above the header on every page (plan 0.2). --}}
@php
    $ambulance = $site->ambulance_phone;
    $whatsapp = $site->whatsappLink();
@endphp
<div class="bg-primary-deep text-sm text-white">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-x-6 gap-y-1 px-4 py-2 sm:justify-end sm:px-6 lg:px-8">
        @if ($ambulance)
            <a href="{{ $site->telLink($ambulance) }}" class="font-semibold underline-offset-4 hover:underline">
                Ambulance &amp; Emergency: {{ $ambulance }}
            </a>
        @else
            <a href="{{ $site->telLink($site->office_phone) }}" class="font-semibold underline-offset-4 hover:underline">
                Call us: {{ $site->office_phone }}
            </a>
        @endif

        @if ($whatsapp)
            <a href="{{ $whatsapp }}" class="underline-offset-4 hover:underline" rel="noopener">
                WhatsApp: {{ $site->whatsapp_number }}
            </a>
        @endif
    </div>
</div>
