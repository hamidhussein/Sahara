@php $field = 'mt-1 block min-h-12 w-full rounded-button border border-line bg-surface px-3 text-base text-ink'; @endphp
<div>
    @if ($sent)
        <div class="rounded-card border border-primary bg-primary-tint p-6" role="status">
            <h3 class="text-lg font-semibold text-primary-dark">Thank you for writing to us.</h3>
            <p class="mt-2 text-ink">We will reply as soon as we can. In an emergency, please call us.</p>
            <button type="button" wire:click="$set('sent', false)" class="btn-outline mt-4 text-primary hover:bg-primary-tint">Send another message</button>
        </div>
    @else
        <form wire:submit="send" class="space-y-4" novalidate>
            <label class="block">
                <span class="font-medium text-ink">Name</span>
                <input type="text" autocomplete="name" wire:model="name" class="{{ $field }}" @error('name') aria-invalid="true" @enderror>
                @error('name') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
            </label>
            <label class="block">
                <span class="font-medium text-ink">Email or phone</span>
                <input type="text" autocomplete="email" wire:model="contact" class="{{ $field }}" @error('contact') aria-invalid="true" @enderror>
                @error('contact') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
            </label>
            <label class="block">
                <span class="font-medium text-ink">Subject</span>
                <input type="text" wire:model="subject" class="{{ $field }}" @error('subject') aria-invalid="true" @enderror>
                @error('subject') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
            </label>
            <label class="block">
                <span class="font-medium text-ink">Message</span>
                <textarea rows="6" wire:model="message" class="{{ $field }} py-2" @error('message') aria-invalid="true" @enderror></textarea>
                @error('message') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
            </label>

            {{-- Spam trap: hidden from people and screen readers --}}
            <div class="hidden" aria-hidden="true">
                <label>Website <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <button type="submit" class="btn-primary text-lg" wire:loading.attr="disabled" wire:target="send">
                <span wire:loading.remove wire:target="send">Send message</span>
                <span wire:loading wire:target="send">Sending…</span>
            </button>
        </form>
    @endif
</div>
