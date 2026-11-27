@php
    $menu = collect(config('sswf.menu'))->map(function ($item) {
        $children = $item['children'] ?? [];
        if ($children === 'programs') {
            $children = \App\Models\Program::published()->ordered()->get(['name', 'slug'])
                ->map(fn ($p) => ['label' => $p->name, 'url' => route('programs.show', $p)])
                ->all();
        } else {
            $children = array_map(fn ($c) => [
                'label' => $c['label'],
                'url' => route($c['route']).(isset($c['fragment']) ? '#'.$c['fragment'] : ''),
            ], $children);
        }

        return [
            'label' => $item['label'],
            'url' => route($item['route']),
            'active' => request()->routeIs(str($item['route'])->before('.')->append('*')->value()),
            'children' => $children,
        ];
    });
@endphp

<header x-data="{ open: false }" @keydown.escape.window="open = false" class="sticky top-0 z-40 border-b border-line bg-surface">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:h-20 lg:px-8" aria-label="Main">
        <x-site.logo />

        {{-- Desktop menu --}}
        <ul class="hidden items-center gap-1 lg:flex">
            @foreach ($menu as $item)
                <li class="relative" @if ($item['children']) x-data="{ flyout: false }" @mouseleave="flyout = false" @endif>
                    @if ($item['children'])
                        <button type="button"
                                @click="flyout = !flyout" @mouseenter="flyout = true"
                                :aria-expanded="flyout.toString()"
                                class="flex min-h-11 items-center gap-1 rounded-button px-3 text-[15px] font-medium {{ $item['active'] ? 'text-primary' : 'text-ink hover:text-primary' }}">
                            {{ $item['label'] }}
                            <svg class="size-4 transition-transform" :class="flyout && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.2 7.2a.75.75 0 0 1 1.06 0L10 10.94l3.74-3.74a.75.75 0 1 1 1.06 1.06l-4.27 4.27a.75.75 0 0 1-1.06 0L5.2 8.26a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="flyout" x-cloak x-transition.opacity.duration.150ms @click.outside="flyout = false"
                             class="absolute left-0 top-full z-10 pt-2">
                            <ul class="{{ count($item['children']) > 4 ? 'grid w-[34rem] grid-cols-2' : 'w-60' }} gap-1 rounded-card border border-line bg-surface p-2 shadow-lg">
                                @foreach ($item['children'] as $child)
                                    <li>
                                        <a href="{{ $child['url'] }}" class="block rounded-button px-3 py-2.5 text-[15px] text-ink hover:bg-primary-tint hover:text-primary-dark">{{ $child['label'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <a href="{{ $item['url'] }}"
                           @if ($item['active']) aria-current="page" @endif
                           class="flex min-h-11 items-center rounded-button px-3 text-[15px] font-medium {{ $item['active'] ? 'text-primary' : 'text-ink hover:text-primary' }}">
                            {{ $item['label'] }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="flex items-center gap-2">
            <a href="{{ route('donate') }}" class="btn-donate hidden sm:inline-flex">Donate</a>

            <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="mobile-menu"
                    class="grid size-11 place-items-center rounded-button text-ink hover:bg-primary-tint lg:hidden">
                <span class="sr-only" x-text="open ? 'Close menu' : 'Open menu'">Open menu</span>
                <svg x-show="!open" class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="open" x-cloak class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>
    </nav>

    {{-- Mobile menu --}}
    <div id="mobile-menu" x-show="open" x-cloak class="max-h-[calc(100dvh-4rem)] overflow-y-auto border-t border-line bg-surface lg:hidden">
        <ul class="space-y-1 px-4 py-4">
            @foreach ($menu as $item)
                <li @if ($item['children']) x-data="{ sub: false }" @endif>
                    @if ($item['children'])
                        <button type="button" @click="sub = !sub" :aria-expanded="sub.toString()"
                                class="flex min-h-12 w-full items-center justify-between rounded-button px-3 text-lg font-medium text-ink hover:bg-primary-tint">
                            {{ $item['label'] }}
                            <svg class="size-5 transition-transform" :class="sub && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.2 7.2a.75.75 0 0 1 1.06 0L10 10.94l3.74-3.74a.75.75 0 1 1 1.06 1.06l-4.27 4.27a.75.75 0 0 1-1.06 0L5.2 8.26a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                        </button>
                        <ul x-show="sub" x-cloak class="mb-2 ml-3 border-l-2 border-primary-tint pl-3">
                            <li><a href="{{ $item['url'] }}" class="flex min-h-11 items-center px-3 font-semibold text-primary">All {{ strtolower($item['label']) }}</a></li>
                            @foreach ($item['children'] as $child)
                                <li><a href="{{ $child['url'] }}" class="flex min-h-11 items-center px-3 text-ink">{{ $child['label'] }}</a></li>
                            @endforeach
                        </ul>
                    @else
                        <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif
                           class="flex min-h-12 items-center rounded-button px-3 text-lg font-medium {{ $item['active'] ? 'text-primary' : 'text-ink' }} hover:bg-primary-tint">
                            {{ $item['label'] }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</header>
