<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Start here</x-slot>
        <x-slot name="description">Shortcuts for your day-to-day work.</x-slot>

        <nav class="admin-shortcuts" aria-label="Common admin tasks">
            @foreach ($links as $link)
                <a href="{{ $link['url'] }}" class="admin-shortcut">
                    <span class="admin-shortcut-icon"><x-filament::icon :icon="$link['icon']" class="size-5" /></span>
                    <span>
                        <span class="admin-shortcut-label">{{ $link['label'] }}</span>
                        <span class="admin-shortcut-description">{{ $link['description'] }}</span>
                    </span>
                    <x-filament::icon icon="heroicon-o-chevron-right" class="admin-shortcut-arrow size-4" />
                </a>
            @endforeach
        </nav>
    </x-filament::section>
</x-filament-widgets::widget>
