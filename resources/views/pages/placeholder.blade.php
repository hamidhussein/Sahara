<x-layouts.app :title="$title" :description="$lede">
    <x-site.page-header :title="$title" :lede="$lede" :breadcrumbs="$breadcrumbs" />

    <section class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <p class="text-lg text-ink-muted">This page is being built and will be ready before the website launches.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('home') }}" class="btn-primary">Go to the home page</a>
            <a href="{{ route('contact') }}" class="btn-outline text-primary hover:bg-primary-tint">Contact us</a>
        </div>
    </section>
</x-layouts.app>
