{{-- Plan 1.1: "This page has wandered off." --}}
<x-layouts.app title="Page not found" :donateBand="false" noindex>
    <section class="mx-auto max-w-2xl px-4 py-20 text-center sm:px-6 lg:py-28">
        <p class="text-6xl font-bold text-primary" aria-hidden="true">404</p>
        <h1 class="mt-4 text-[1.75rem] font-bold leading-tight text-ink md:text-4xl">This page has wandered off.</h1>
        <p class="mt-4 text-lg text-ink-muted">The page you are looking for may have moved. Try one of these:</p>
        <ul class="mt-8 flex flex-wrap justify-center gap-3">
            <li><a href="{{ route('home') }}" class="btn-primary">Home</a></li>
            <li><a href="{{ route('donate') }}" class="btn-donate">Donate</a></li>
            <li><a href="{{ route('get-help') }}" class="btn-outline text-primary hover:bg-primary-tint">Get Help</a></li>
            <li><a href="{{ route('contact') }}" class="btn-outline text-primary hover:bg-primary-tint">Contact</a></li>
        </ul>
    </section>
</x-layouts.app>
