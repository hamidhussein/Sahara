<x-layouts.app title="Payment did not go through" :donateBand="false" noindex>
    <section class="mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:py-24">
        <div class="rounded-card border border-line bg-surface p-6 sm:p-10">
            <h1 class="text-[1.75rem] font-bold leading-tight text-ink md:text-4xl">Your payment did not go through</h1>
            <p class="mt-4 text-lg text-ink">
                No money has been taken. You can try again, choose another payment method, or
                @if ($site->whatsappLink())
                    message us on <a href="{{ $site->whatsappLink() }}" class="font-medium text-water-dark underline">WhatsApp {{ $site->whatsapp_number }}</a>
                @else
                    call us on <a href="{{ $site->telLink($site->office_phone) }}" class="font-medium text-water-dark underline">{{ $site->office_phone }}</a>
                @endif
                for help.
            </p>
            <p class="mt-2 text-ink-muted">Reference {{ $donation->reference }}</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('donate', array_filter(['type' => $donation->givingType->slug, 'cause' => $donation->cause?->slug, 'amount' => $donation->amount])) }}" class="btn-donate">Try again or choose another method</a>
                <a href="{{ route('contact') }}" class="btn-outline text-primary hover:bg-primary-tint">Contact us</a>
            </div>
        </div>
    </section>
</x-layouts.app>
