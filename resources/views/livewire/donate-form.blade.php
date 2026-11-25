@php
    $steps = [1 => 'Type of giving', 2 => 'Cause', 3 => 'Amount', 4 => 'Payment'];
    $tile = fn (bool $on) => $on
        ? 'border-primary bg-primary-tint ring-2 ring-primary'
        : 'border-line bg-surface hover:border-primary';
    $chip = fn (bool $on) => $on
        ? 'border-primary bg-primary text-white'
        : 'border-line bg-surface text-ink hover:border-primary';
    $fieldClass = 'mt-1 block min-h-12 w-full rounded-button border border-line bg-surface px-3 text-base text-ink';
    $bank = $this->settings;
@endphp

<div class="grid gap-8 lg:grid-cols-3 lg:gap-12">
    {{-- Summary and trust panel: right on desktop, top on phones --}}
    <aside class="lg:order-last" aria-label="Your donation">
        <div class="rounded-card border border-line bg-surface p-5 lg:sticky lg:top-28">
            <h2 class="text-lg font-bold text-primary">Your donation</h2>
            <dl class="mt-3 space-y-2">
                <div class="flex justify-between gap-4">
                    <dt class="text-ink-muted">Amount</dt>
                    <dd class="text-xl font-bold text-ink" aria-live="polite">{{ $this->amount ? 'Rs '.number_format($this->amount) : '–' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-ink-muted">Type</dt>
                    <dd class="text-end font-medium">{{ $this->type?->name ?? '–' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-ink-muted">Cause</dt>
                    <dd class="text-end font-medium">{{ $this->cause?->name ?? 'Where it is needed most' }}</dd>
                </div>
            </dl>

            @if ($this->causeNeedsReview)
                <p class="mt-4 rounded-button bg-primary-tint p-3 text-sm text-ink">
                    {{ $this->type->name }} can only go to causes where it is religiously allowed. We will contact you before using your gift for {{ $this->cause->name }}.
                </p>
            @endif

            <ul class="mt-5 hidden space-y-2 border-t border-line pt-4 text-sm text-ink-muted lg:block">
                <li>Secure payment through trusted Pakistani payment providers.</li>
                <li>Registered since 2013, Reg. No. {{ $site->registration_number }}.</li>
                <li>Your Zakat and Khums are kept in separate funds and used only for eligible purposes.</li>
                @if ($site->whatsappLink())
                    <li>Questions? <a href="{{ $site->whatsappLink() }}" class="font-medium text-water-dark underline">WhatsApp {{ $site->whatsapp_number }}</a></li>
                @else
                    <li>Questions? Call <a href="{{ $site->telLink($site->office_phone) }}" class="font-medium text-water-dark underline">{{ $site->office_phone }}</a></li>
                @endif
            </ul>
        </div>
    </aside>

    <div class="lg:col-span-2">
        {{-- Step indicator --}}
        <nav aria-label="Donation steps">
            <ol class="grid grid-cols-4 gap-2">
                @foreach ($steps as $n => $label)
                    <li>
                        <button type="button" wire:click="goTo({{ $n }})" @if ($step === $n) aria-current="step" @endif
                                class="flex min-h-11 w-full flex-col items-start gap-1 border-t-4 pt-2 text-start text-sm {{ $step >= $n ? 'border-primary text-primary' : 'border-line text-ink-muted' }}">
                            <span class="font-semibold">{{ $n }}</span>
                            <span class="hidden sm:block">{{ $label }}</span>
                        </button>
                    </li>
                @endforeach
            </ol>
        </nav>

        <form wire:submit="donate" class="mt-8" novalidate>
            {{-- Step 1: type of giving --}}
            @if ($step === 1)
                <fieldset>
                    <legend class="text-[1.375rem] font-bold text-ink">Choose your type of giving</legend>
                    @error('typeSlug') <p class="mt-2 font-medium text-emergency" role="alert">{{ $message }}</p> @enderror
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach ($this->types as $type)
                            <button type="button" wire:click="chooseType('{{ $type->slug }}')" aria-pressed="{{ $typeSlug === $type->slug ? 'true' : 'false' }}"
                                    class="rounded-card border p-4 text-start {{ $tile($typeSlug === $type->slug) }}">
                                <span class="block text-lg font-semibold text-ink">{{ $type->name }}</span>
                                @if ($type->description)
                                    <span class="mt-1 block text-ink-muted">{{ $type->description }}</span>
                                @endif
                                @if ($type->slug === 'fitrana' && $bank->fitrana_amount)
                                    <span class="mt-2 block font-semibold text-primary">This year: Rs {{ number_format($bank->fitrana_amount) }} per person</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </fieldset>
            @endif

            {{-- Step 2: cause --}}
            @if ($step === 2)
                <fieldset>
                    <legend class="text-[1.375rem] font-bold text-ink">Choose a cause</legend>
                    <p class="mt-2 text-ink-muted">Optional. Leave it blank and we use your gift where it is needed most.</p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($this->causes as $cause)
                            <button type="button" wire:click="chooseCause('{{ $cause->slug }}')" aria-pressed="{{ $causeSlug === $cause->slug ? 'true' : 'false' }}"
                                    class="min-h-11 rounded-full border px-4 font-medium {{ $chip($causeSlug === $cause->slug) }}">
                                {{ $cause->name }}
                            </button>
                        @endforeach
                    </div>
                    @if ($this->type?->is_restricted)
                        <p class="mt-5 rounded-card bg-primary-tint p-4 text-ink">
                            {{ $this->type->name }} can only go to causes where it is religiously allowed.
                            @if ($this->causeNeedsReview)
                                <strong>We will contact you before using your gift for {{ $this->cause->name }}.</strong>
                            @endif
                        </p>
                    @endif
                </fieldset>
            @endif

            {{-- Step 3: amount and donor details --}}
            @if ($step === 3)
                <fieldset>
                    <legend class="text-[1.375rem] font-bold text-ink">Choose an amount</legend>

                    @if ($this->type?->slug === 'fitrana' && $bank->fitrana_amount)
                        <label class="mt-5 block max-w-xs">
                            <span class="font-medium text-ink">Number of people</span>
                            <input type="number" min="1" max="100" inputmode="numeric" wire:model.live.debounce.300ms="fitranaPeople" class="{{ $fieldClass }}">
                            <span class="mt-1 block text-sm text-ink-muted">Rs {{ number_format($bank->fitrana_amount) }} per person this year.</span>
                        </label>
                    @endif

                    @unless ($this->type?->slug === 'fitrana' && $fitranaPeople && $bank->fitrana_amount)
                        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-5">
                            @foreach ($presets as $preset)
                                <button type="button" wire:click="$set('amountChoice', '{{ $preset }}')" aria-pressed="{{ $amountChoice === (string) $preset ? 'true' : 'false' }}"
                                        class="min-h-12 rounded-button border px-3 font-semibold {{ $chip($amountChoice === (string) $preset) }}">
                                    Rs {{ number_format($preset) }}
                                </button>
                            @endforeach
                            <button type="button" wire:click="$set('amountChoice', 'other')" aria-pressed="{{ $amountChoice === 'other' ? 'true' : 'false' }}"
                                    class="col-span-2 min-h-12 rounded-button border px-3 font-semibold sm:col-span-1 {{ $chip($amountChoice === 'other') }}">
                                Other amount
                            </button>
                        </div>
                        @if ($amountChoice === 'other')
                            <label class="mt-4 block max-w-xs">
                                <span class="font-medium text-ink">Amount in rupees</span>
                                <input type="text" inputmode="numeric" autocomplete="off" wire:model.live.debounce.400ms="customAmount" class="{{ $fieldClass }}" placeholder="e.g. 2500">
                            </label>
                        @endif
                    @endunless
                    @error('amount') <p class="mt-2 font-medium text-emergency" role="alert">{{ $message }}</p> @enderror
                </fieldset>

                <fieldset class="mt-10">
                    <legend class="text-lg font-bold text-ink">Your details</legend>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label class="block sm:col-span-2">
                            <span class="font-medium text-ink">Full name</span>
                            <input type="text" autocomplete="name" wire:model.blur="name" class="{{ $fieldClass }}" @error('name') aria-invalid="true" @enderror>
                            @error('name') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
                        </label>
                        <label class="block">
                            <span class="font-medium text-ink">Mobile number</span>
                            <input type="tel" autocomplete="tel" inputmode="tel" wire:model.blur="phone" class="{{ $fieldClass }}" placeholder="0300 1234567" @error('phone') aria-invalid="true" @enderror>
                            @error('phone') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
                        </label>
                        <label class="block">
                            <span class="font-medium text-ink">Email <span class="font-normal text-ink-muted">(for your receipt)</span></span>
                            <input type="email" autocomplete="email" wire:model.blur="email" class="{{ $fieldClass }}" @error('email') aria-invalid="true" @enderror>
                            @error('email') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
                        </label>
                        <label class="flex min-h-11 items-center gap-3 sm:col-span-2">
                            <input type="checkbox" wire:model="anonymous" class="size-5 accent-primary">
                            <span>Give anonymously</span>
                        </label>
                    </div>
                </fieldset>
            @endif

            {{-- Step 4: payment --}}
            @if ($step === 4)
                <fieldset>
                    <legend class="text-[1.375rem] font-bold text-ink">Choose how to pay</legend>
                    @error('method') <p class="mt-2 font-medium text-emergency" role="alert">{{ $message }}</p> @enderror
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach ($methods as $m)
                            <label class="flex min-h-14 cursor-pointer items-center gap-3 rounded-card border p-4 {{ $tile($method === $m->value) }}">
                                <input type="radio" name="method" value="{{ $m->value }}" wire:model.live="method" class="size-5 accent-primary">
                                <span class="text-lg font-semibold text-ink">{{ $m->getLabel() }}</span>
                            </label>
                        @endforeach
                    </div>

                    @if ($method === 'bank_transfer')
                        <div class="mt-6 rounded-card border border-line bg-surface p-5">
                            <h3 class="text-lg font-semibold text-ink">Please transfer to</h3>
                            <dl class="mt-3 grid gap-x-6 gap-y-2 sm:grid-cols-[auto_1fr]">
                                @if ($bank->account_title)
                                    <dt class="text-ink-muted">Account title</dt><dd class="font-medium">{{ $bank->account_title }}</dd>
                                @endif
                                <dt class="text-ink-muted">Bank</dt><dd class="font-medium">{{ $bank->bank_name }}</dd>
                                <dt class="text-ink-muted">Account number</dt><dd class="font-mono font-medium">{{ $bank->account_number }}</dd>
                                @if ($bank->iban)
                                    <dt class="text-ink-muted">IBAN</dt><dd class="font-mono font-medium">{{ $bank->iban }}</dd>
                                @endif
                            </dl>
                            <p class="mt-4 text-ink">Then upload a screenshot or photo of your receipt below. We will confirm your donation and email your receipt within 24 hours.</p>

                            <label class="mt-4 block">
                                <span class="font-medium text-ink">Transfer receipt</span>
                                <input type="file" wire:model="receipt" accept="image/jpeg,image/png,image/webp,application/pdf"
                                       class="mt-1 block w-full rounded-button border border-line bg-canvas p-2 file:me-3 file:min-h-10 file:rounded-button file:border-0 file:bg-primary file:px-4 file:font-semibold file:text-white">
                                <span wire:loading wire:target="receipt" class="mt-1 block text-ink-muted">Uploading…</span>
                                @error('receipt') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
                            </label>

                            @if ($bank->iban && $bank->swift_code)
                                <p class="mt-4 text-sm text-ink-muted">Overseas donors: send an international transfer to IBAN {{ $bank->iban }}, SWIFT {{ $bank->swift_code }}.</p>
                            @endif
                        </div>
                    @endif
                </fieldset>
            @endif

            {{-- Navigation --}}
            <div class="mt-10 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                @if ($step > 1)
                    <button type="button" wire:click="back" class="btn-outline text-primary hover:bg-primary-tint">Back</button>
                @else
                    <span></span>
                @endif

                @if ($step < 4)
                    <button type="button" wire:click="next" class="btn-primary text-lg">
                        {{ $step === 2 && ! $causeSlug ? 'Skip, use it where needed' : 'Continue' }}
                    </button>
                @else
                    <button type="submit" class="btn-donate text-lg" wire:loading.attr="disabled" wire:target="donate,receipt">
                        <span wire:loading.remove wire:target="donate">Donate {{ $this->amount ? 'Rs '.number_format($this->amount) : '' }}</span>
                        <span wire:loading wire:target="donate">Sending…</span>
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
