@php
    $field = 'mt-1 block min-h-12 w-full rounded-button border border-line bg-surface px-3 text-base text-ink';
    $emergency = $site->ambulance_phone ?: $site->office_phone;
@endphp
<div>
    @if ($reference)
        <div class="rounded-card border border-primary bg-primary-tint p-6 md:p-8" role="status" tabindex="-1" x-init="$el.focus(); $el.scrollIntoView({ block: 'center' })">
            <h2 class="text-xl font-bold text-primary-dark">Thank you. We have received your request.</h2>
            <p class="mt-3 text-lg text-ink">Your reference is <strong class="font-mono">{{ $reference }}</strong>. Please keep it. A member of our team will call you as soon as possible.</p>
            <p class="mt-2 text-ink">For emergencies, please call <a href="{{ $site->telLink($emergency) }}" class="font-semibold underline">{{ $emergency }}</a>.</p>
            <p class="mt-4 text-sm text-ink-muted">All requests are private and seen only by our services team.</p>
            <button type="button" wire:click="$set('reference', null)" class="btn-outline mt-5 text-primary hover:bg-primary-tint">Send another request</button>
        </div>
    @else
        {{-- Help cards: choosing one sets the kind of request --}}
        <fieldset>
            <legend class="text-[1.375rem] font-bold leading-snug text-primary md:text-[1.75rem]">What do you need help with?</legend>
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($types as $option)
                    @php $active = $chosen === $option; @endphp
                    <button type="button" wire:click="choose('{{ $option->value }}')" aria-pressed="{{ $active ? 'true' : 'false' }}"
                        class="flex flex-col rounded-card border-2 p-5 text-start transition-colors {{ $active ? 'border-primary bg-primary-tint' : 'border-line bg-surface hover:border-primary/50' }}">
                        <span class="flex items-center justify-between gap-3">
                            <span class="text-lg font-semibold text-ink">{{ $option->getLabel() }}</span>
                            <span class="grid size-6 shrink-0 place-items-center rounded-full border-2 {{ $active ? 'border-primary bg-primary text-white' : 'border-line' }}" aria-hidden="true">
                                @if ($active)<svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.6l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>@endif
                            </span>
                        </span>
                        <span class="mt-2 text-ink-muted">{{ $option->description() }}</span>
                    </button>
                @endforeach
            </div>
            @error('type') <p class="mt-3 font-medium text-emergency">{{ $message }}</p> @enderror
        </fieldset>

        <form wire:submit="send" class="mt-10 space-y-5 rounded-card border border-line bg-surface p-5 md:p-8" novalidate>
            <h2 class="text-xl font-bold text-primary">{{ $chosen ? 'Request: '.$chosen->getLabel() : 'Your request' }}</h2>

            <div class="grid gap-5 md:grid-cols-2">
                <label class="block">
                    <span class="font-medium text-ink">Your name</span>
                    <input type="text" autocomplete="name" wire:model="name" class="{{ $field }}" @error('name') aria-invalid="true" @enderror>
                    @error('name') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
                </label>
                <label class="block">
                    <span class="font-medium text-ink">Mobile number</span>
                    <input type="tel" inputmode="tel" autocomplete="tel" placeholder="0300 1234567" wire:model="phone" class="{{ $field }}" @error('phone') aria-invalid="true" @enderror>
                    @error('phone') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
                </label>
                <label class="block">
                    <span class="font-medium text-ink">District</span>
                    <select wire:model="district" class="{{ $field }}" @error('district') aria-invalid="true" @enderror>
                        <option value="">Choose your district</option>
                        @foreach (config('sswf.districts') as $name)
                            <option value="{{ $name }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('district') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
                </label>
                <label class="block">
                    <span class="font-medium text-ink">Village or area <span class="font-normal text-ink-muted">(optional)</span></span>
                    <input type="text" wire:model="village" class="{{ $field }}">
                </label>
            </div>

            <label class="block">
                <span class="font-medium text-ink">Person in need <span class="font-normal text-ink-muted">(if not you)</span></span>
                <input type="text" wire:model="personInNeed" placeholder="Their name and how you know them" class="{{ $field }}">
            </label>

            <label class="block">
                <span class="font-medium text-ink">Tell us what you need</span>
                <textarea rows="5" wire:model="details" placeholder="{{ $chosen?->prompt() ?? 'What is needed, for whom, and by when.' }}" class="{{ $field }} py-2" @error('details') aria-invalid="true" @enderror></textarea>
                @error('details') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
            </label>

            <label class="block">
                <span class="font-medium text-ink">Upload a document <span class="font-normal text-ink-muted">(optional)</span></span>
                <span class="mt-1 block text-sm text-ink-muted">A prescription, hospital slip or school letter helps us act faster. Photo or PDF, up to 5 MB.</span>
                <input type="file" wire:model="document" accept="image/jpeg,image/png,image/webp,application/pdf" class="mt-2 block w-full text-base file:me-3 file:rounded-button file:border-0 file:bg-primary-tint file:px-4 file:py-2 file:font-semibold file:text-primary">
                <span wire:loading wire:target="document" class="mt-1 block text-ink-muted">Uploading…</span>
                @error('document') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
            </label>

            <label class="flex items-start gap-3">
                <input type="checkbox" wire:model="consent" class="mt-1 size-5 shrink-0 rounded border-line text-primary" @error('consent') aria-invalid="true" @enderror>
                <span>I agree that SSWF may contact me and verify this request.</span>
            </label>
            @error('consent') <span class="block text-emergency">{{ $message }}</span> @enderror

            {{-- Spam trap: hidden from people and screen readers --}}
            <div class="hidden" aria-hidden="true">
                <label>Website <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <button type="submit" class="btn-primary text-lg" wire:loading.attr="disabled" wire:target="send,document">
                <span wire:loading.remove wire:target="send">Send request</span>
                <span wire:loading wire:target="send">Sending…</span>
            </button>
            <p class="text-sm text-ink-muted">All requests are private and seen only by our services team. Your phone number is never shown publicly.</p>
        </form>
    @endif
</div>
