@php
    $fields = [
        'cash' => 'Cash and bank balance',
        'gold' => 'Gold (value)',
        'silver' => 'Silver (value)',
        'business' => 'Business stock and money owed to you',
        'debts' => 'Debts you owe now',
    ];
@endphp
<div class="grid gap-8 lg:grid-cols-5">
    <form class="space-y-4 lg:col-span-3" wire:submit.prevent>
        @foreach ($fields as $key => $label)
            <label class="block">
                <span class="font-medium text-ink">{{ $label }}</span>
                <span class="relative mt-1 block">
                    <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-ink-muted">Rs</span>
                    <input type="text" inputmode="numeric" autocomplete="off" wire:model.live.debounce.300ms="{{ $key }}"
                           class="block min-h-12 w-full rounded-button border border-line bg-surface ps-10 pe-3 text-base">
                </span>
            </label>
        @endforeach
    </form>

    <div class="lg:col-span-2">
        <div class="rounded-card bg-primary p-6 text-white lg:sticky lg:top-28" aria-live="polite">
            <p class="text-white/85">Your Zakat due</p>
            <p class="mt-1 text-4xl font-bold">{{ $this->zakat !== null ? 'Rs '.number_format($this->zakat) : 'Rs 0' }}</p>

            @if ($this->zakat === 0)
                <p class="mt-3 text-white/90">Your zakatable wealth (Rs {{ number_format($this->wealth) }}) is below the nisab of Rs {{ number_format($this->nisab) }}, so no Zakat is due.</p>
            @elseif ($this->zakat)
                <p class="mt-3 text-white/90">2.5% of Rs {{ number_format($this->wealth) }}.</p>
                @if ($this->nisab === null)
                    <p class="mt-2 text-white/90">Zakat is only due if your wealth is above the nisab. Ask us or a scholar for this year’s nisab.</p>
                @endif
                <a href="{{ route('donate', ['type' => 'zakat', 'amount' => $this->zakat]) }}" class="btn-donate mt-5 w-full">Pay this as Zakat</a>
            @else
                <p class="mt-3 text-white/90">Enter what you own today.</p>
            @endif
        </div>
    </div>
</div>
