<x-layouts.app title="Zakat calculator" description="Work out how much Zakat you owe and pay it to Subka Sahara Welfare Foundation, where Zakat is kept in a separate fund for eligible families.">
    <x-site.page-header title="Zakat calculator" lede="Not sure how much Zakat you owe? Enter what you own today; we will calculate 2.5% of your zakatable wealth if it is above the nisab."
        :breadcrumbs="['Donate' => route('donate'), 'Zakat calculator' => null]" />

    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <livewire:zakat-calculator />
        <p class="mt-10 text-ink-muted">This calculator is a guide; for specific questions, please consult a scholar.</p>
    </section>
</x-layouts.app>
