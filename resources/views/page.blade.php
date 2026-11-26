{{-- Editable text page (Admin → Content → Pages), e.g. the legal pages: a plain readable column. --}}
<x-layouts.app :title="$page->seo_title ?: $page->title" :description="$page->seo_description ?: $page->lede" :urdu="$page->language->isRtl()">
    <x-site.page-header :title="$page->title" :lede="$page->lede" :breadcrumbs="[$page->title => null]" />

    <article class="mx-auto max-w-[760px] px-4 py-12 sm:px-6 lg:py-16">
        <x-rich-text :html="$page->body" :language="$page->language" />
        <p class="mt-12 border-t border-line pt-4 text-ink-muted">Last updated {{ $page->updated_at->format('j F Y') }}.</p>
    </article>
</x-layouts.app>
