@php $field = 'mt-1 block min-h-12 w-full rounded-button border border-line bg-surface px-3 text-base text-ink'; @endphp
<div>
    @if ($sent)
        <div class="rounded-card border border-primary bg-primary-tint p-6" role="status">
            <h3 class="text-lg font-semibold text-primary-dark">Thank you for registering.</h3>
            <p class="mt-2 text-ink">You are now part of the Sahara Blood Donor Society. We will call you only when a patient near you needs your blood group.</p>
            <button type="button" wire:click="$set('sent', false)" class="btn-outline mt-4 text-primary hover:bg-primary-tint">Register someone else</button>
        </div>
    @else
        <form wire:submit="register" class="space-y-4" novalidate>
            <label class="block">
                <span class="font-medium text-ink">Full name</span>
                <input type="text" autocomplete="name" wire:model="name" class="{{ $field }}" @error('name') aria-invalid="true" @enderror>
                @error('name') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
            </label>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="font-medium text-ink">Blood group</span>
                    <select wire:model="bloodGroup" class="{{ $field }}" @error('bloodGroup') aria-invalid="true" @enderror>
                        <option value="">Choose</option>
                        @foreach ($groups as $group)
                            <option value="{{ $group->value }}">{{ $group->value }}</option>
                        @endforeach
                    </select>
                    @error('bloodGroup') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
                </label>
                <label class="block">
                    <span class="font-medium text-ink">District</span>
                    <select wire:model="district" class="{{ $field }}" @error('district') aria-invalid="true" @enderror>
                        <option value="">Choose</option>
                        @foreach (config('sswf.districts') as $name)
                            <option value="{{ $name }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('district') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
                </label>
            </div>
            <label class="block">
                <span class="font-medium text-ink">Mobile number</span>
                <input type="tel" inputmode="tel" autocomplete="tel" placeholder="0300 1234567" wire:model="phone" class="{{ $field }}" @error('phone') aria-invalid="true" @enderror>
                @error('phone') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
            </label>
            <label class="block">
                <span class="font-medium text-ink">Last donation date <span class="font-normal text-ink-muted">(optional)</span></span>
                <input type="date" wire:model="lastDonatedOn" max="{{ now()->toDateString() }}" class="{{ $field }}" @error('lastDonatedOn') aria-invalid="true" @enderror>
                @error('lastDonatedOn') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
            </label>
            <label class="flex items-start gap-3">
                <input type="checkbox" wire:model="consent" class="mt-1 size-5 shrink-0 rounded border-line text-primary" @error('consent') aria-invalid="true" @enderror>
                <span>I agree to be contacted in emergencies.</span>
            </label>
            @error('consent') <span class="block text-emergency">{{ $message }}</span> @enderror

            <div class="hidden" aria-hidden="true">
                <label>Website <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <button type="submit" class="btn-primary w-full text-lg sm:w-auto" wire:loading.attr="disabled" wire:target="register">
                <span wire:loading.remove wire:target="register">Register as a donor</span>
                <span wire:loading wire:target="register">Sending…</span>
            </button>
            <p class="text-sm text-ink-muted">Your phone number is never shown publicly. Only our services team can see it.</p>
        </form>
    @endif
</div>
