{{-- Stand-in for a provider's hosted checkout page. Local and testing only. --}}
<x-layouts.app title="Test payment" :donateBand="false">
    <section class="mx-auto max-w-lg px-4 py-16">
        <div class="rounded-card border-2 border-dashed border-water bg-surface p-8">
            <p class="text-sm font-semibold text-water-dark">Test payment provider: no real money moves</p>
            <h1 class="mt-2 text-2xl font-bold text-ink">Pay {{ $donation->formattedAmount() }}</h1>
            <p class="mt-1 text-ink-muted">{{ $donation->payment_method->getLabel() }} · {{ $donation->reference }}</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                @foreach (['approve' => ['Approve payment', 'btn-primary'], 'decline' => ['Decline', 'btn-outline text-emergency']] as $key => [$label, $class])
                    <form method="POST" action="{{ route('payments.callback', $donation->payment_method->value) }}">
                        @foreach ($$key as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <button type="submit" class="{{ $class }} w-full">{{ $label }}</button>
                    </form>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.app>
