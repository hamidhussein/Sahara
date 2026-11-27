@props(['member'])
@if ($photo = $member->imageUrl('photo', 'thumb'))
    <img src="{{ $photo }}" alt="{{ $member->name }}" loading="lazy" {{ $attributes->class(['shrink-0 rounded-full object-cover']) }}>
@else
    <span {{ $attributes->class(['grid shrink-0 place-items-center rounded-full bg-primary-tint font-bold text-primary']) }} aria-hidden="true">{{ $member->initials() }}</span>
@endif
