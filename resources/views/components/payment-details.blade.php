{{-- How to pay by bank, JazzCash or Easypaisa: details, QR code and steps. --}}
@props(['details', 'amount' => 0])
<div class="rounded-card border border-line bg-surface p-5">
    @if ($details->isSample)
        <p class="mb-4 rounded-button border-2 border-dashed border-emergency px-3 py-2 font-semibold text-emergency" role="note">
            Sample details for testing. Do not send money to these. Real details are entered in Admin → Settings before launch.
        </p>
    @endif

    <div class="grid gap-6 sm:grid-cols-[1fr_auto] sm:items-start">
        <div>
            <h3 class="text-lg font-semibold text-ink">Please send to</h3>
            <dl class="mt-3 grid gap-x-6 gap-y-2 sm:grid-cols-[auto_1fr]">
                @foreach ($details->rows as $label => $value)
                    <dt class="text-ink-muted">{{ $label }}</dt>
                    <dd class="{{ str_contains($label, 'number') || in_array($label, ['IBAN', 'Raast ID'], true) ? 'font-mono' : '' }} font-medium break-all">{{ $value }}</dd>
                @endforeach
            </dl>

            @if ($details->copyValue)
                <div x-data="{ copied: false }" class="mt-4">
                    <button type="button" class="btn-outline text-primary hover:bg-primary-tint"
                            @click="navigator.clipboard?.writeText(@js(preg_replace('/\s+/', '', $details->copyValue))).then(() => { copied = true; setTimeout(() => copied = false, 2500) })">
                        <span x-show="!copied">Copy {{ $details->method === \App\Enums\PaymentMethod::BankTransfer ? (str_starts_with($details->copyValue, 'PK') ? 'IBAN' : 'account number') : 'number' }}</span>
                        <span x-show="copied" x-cloak>Copied</span>
                    </button>
                </div>
            @endif
        </div>

        @if ($details->qrUrl)
            <figure class="justify-self-center text-center sm:justify-self-end">
                <img src="{{ $details->qrUrl }}" alt="QR code to pay with {{ $details->method->getLabel() }}" width="168" height="168"
                     class="size-42 rounded-button border border-line bg-white p-2">
                <figcaption class="mt-1 text-sm text-ink-muted">Scan with your {{ $details->method === \App\Enums\PaymentMethod::BankTransfer ? 'banking' : $details->method->getLabel() }} app</figcaption>
            </figure>
        @endif
    </div>

    <ol class="mt-6 space-y-2 border-t border-line pt-4">
        @foreach ($details->steps($amount) as $i => $step)
            <li class="flex gap-3">
                <span class="grid size-7 shrink-0 place-items-center rounded-full bg-primary-tint font-semibold text-primary-dark" aria-hidden="true">{{ $i + 1 }}</span>
                <span>{{ $step }}</span>
            </li>
        @endforeach
    </ol>
    <p class="mt-4 text-ink-muted">We will confirm your donation and email your receipt within 24 hours.</p>
</div>
