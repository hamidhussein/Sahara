<x-layouts.app title="Our Projects" description="Water plants, eye camps, ration drives and more: every project Sab Ka Sahara Welfare Foundation has built with its donors and volunteers across Gilgit-Baltistan.">
    <x-site.page-header title="Our Projects" lede="Real places, real people, real results." :breadcrumbs="['Projects' => null]" />

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <p class="max-w-3xl text-lg text-ink-muted">
            Every project here was made possible by our donors and volunteers. Filter by program, district or status to see what has been built, what is under way, and what still needs your help.
        </p>

        <livewire:project-browser />
    </section>
</x-layouts.app>
