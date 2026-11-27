@php $field = 'mt-1 block min-h-12 w-full rounded-button border border-line bg-surface px-3 text-base text-ink'; @endphp
<div>
    @if ($sent)
        <div class="rounded-card border border-primary bg-primary-tint p-6" role="status">
            <h3 class="text-lg font-semibold text-primary-dark">Welcome to the Sahara family!</h3>
            <p class="mt-2 text-ink">Our volunteer coordinator will contact you soon.</p>
            <button type="button" wire:click="$set('sent', false)" class="btn-outline mt-4 text-primary hover:bg-primary-tint">Sign up someone else</button>
        </div>
    @else
        <form wire:submit="join" class="space-y-4" novalidate>
            <label class="block">
                <span class="font-medium text-ink">Full name</span>
                <input type="text" autocomplete="name" wire:model="name" class="{{ $field }}" @error('name') aria-invalid="true" @enderror>
                @error('name') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
            </label>
            <label class="block">
                <span class="font-medium text-ink">Mobile number</span>
                <input type="tel" inputmode="tel" autocomplete="tel" placeholder="0300 1234567" wire:model="phone" class="{{ $field }}" @error('phone') aria-invalid="true" @enderror>
                @error('phone') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
            </label>
            <label class="block">
                <span class="font-medium text-ink">Area</span>
                <input type="text" wire:model="area" placeholder="e.g. Jutial, Gilgit" class="{{ $field }}" @error('area') aria-invalid="true" @enderror>
                @error('area') <span class="mt-1 block text-emergency">{{ $message }}</span> @enderror
            </label>
            <fieldset>
                <legend class="font-medium text-ink">Skills <span class="font-normal text-ink-muted">(choose any)</span></legend>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($allSkills as $skill)
                        <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-full border border-line bg-surface px-4 has-[:checked]:border-primary has-[:checked]:bg-primary-tint">
                            <input type="checkbox" value="{{ $skill }}" wire:model="skills" class="size-4 rounded border-line text-primary">
                            <span>{{ $skill }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <label class="block">
                <span class="font-medium text-ink">Availability <span class="font-normal text-ink-muted">(optional)</span></span>
                <input type="text" wire:model="availability" placeholder="e.g. Weekends, or evenings after 5 pm" class="{{ $field }}">
            </label>

            <div class="hidden" aria-hidden="true">
                <label>Website <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <button type="submit" class="btn-primary w-full text-lg sm:w-auto" wire:loading.attr="disabled" wire:target="join">
                <span wire:loading.remove wire:target="join">Join as a volunteer</span>
                <span wire:loading wire:target="join">Sending…</span>
            </button>
        </form>
    @endif
</div>
