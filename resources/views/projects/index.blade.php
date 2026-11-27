<x-layouts.app title="Our Projects" description="Water plants, eye camps, ration drives and more: every project Subka Sahara Welfare Foundation has built with its donors and volunteers across Gilgit-Baltistan.">
    <x-site.page-header title="Our Projects" lede="Real places, real people, real results." :breadcrumbs="['Projects' => null]" />

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <div class="max-w-3xl space-y-3 text-lg text-ink-muted">
            <p>We are committed to improving the quality of life in underserved communities through initiatives focused on clean drinking water, food security, healthcare and education. Our projects support vulnerable families, promote community well-being and create sustainable opportunities across Gilgit-Baltistan.</p>
            <p>Every project here was made possible by our donors and volunteers. Filter by program, district or status to see what has been built, what is under way, and what is coming next.</p>
        </div>

        <livewire:project-browser />
    </section>
</x-layouts.app>
