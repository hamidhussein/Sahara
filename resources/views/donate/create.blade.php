<x-layouts.app title="Donate" description="Give Zakat, Khums, Sadaqah or a general donation to Subka Sahara Welfare Foundation. Your gift becomes water, medicine and food for families in Gilgit-Baltistan." :donateBand="false">
    <x-site.page-header title="Donate" lede="Your gift becomes water, medicine and food for someone today." :breadcrumbs="['Donate' => null]" compact />

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <livewire:donate-form />

        <p class="mt-12 text-ink-muted">
            Not sure how much Zakat you owe?
            <a href="{{ route('donate.zakat-calculator') }}" class="font-medium text-water-dark underline underline-offset-2">Use our Zakat calculator</a>.
        </p>
    </section>
</x-layouts.app>
