@php
    $isTransfer = $donation->status === \App\Enums\DonationStatus::AwaitingConfirmation;
    $share = rawurlencode('I just gave to Subka Sahara Welfare Foundation, serving Gilgit-Baltistan since 2006. Join me: '.route('donate'));
@endphp
<x-layouts.app title="Thank you" :donateBand="false" noindex>
    <section class="mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:py-24">
        <div class="rounded-card border border-line bg-surface p-6 sm:p-10">
            <h1 class="text-[1.75rem] font-bold leading-tight text-primary md:text-4xl">Thank you, {{ $donation->donor_name }}!</h1>

            @if ($isTransfer)
                <p class="mt-4 text-lg text-ink">
                    We have received your payment receipt for <strong>{{ $donation->formattedAmount() }}</strong>.
                    Our Finance team will check it against our account{{ $donation->donor_email ? ' and email your confirmed receipt to '.\App\Support\Mask::email($donation->donor_email) : ', then send your confirmed receipt' }}.
                </p>
                {{-- Receipt submitted ≠ payment confirmed: say which stage the donor is at. --}}
                <ol class="mt-4 space-y-1 text-ink-muted">
                    <li><span class="font-semibold text-primary">✓ Receipt submitted</span></li>
                    <li>○ Payment checked by Finance</li>
                    <li>○ Confirmed receipt sent to you</li>
                </ol>
            @else
                <p class="mt-4 text-lg text-ink">
                    Your donation of <strong>{{ $donation->formattedAmount() }}</strong> as {{ $donation->givingType->name }} for {{ $donation->causeLabel() }} has been received.
                    @if ($donation->donor_email)
                        A receipt has been sent to {{ \App\Support\Mask::email($donation->donor_email) }}.
                    @endif
                </p>
            @endif

            @if ($donation->needs_eligibility_review)
                <p class="mt-4 rounded-card bg-primary-tint p-4 text-ink">
                    {{ $donation->givingType->name }} can only go to causes where it is religiously allowed. We will contact you on {{ \App\Support\Mask::phone($donation->donor_phone) }} before using your gift for {{ $donation->causeLabel() }}.
                </p>
            @endif

            <p class="mt-4 text-lg text-ink">Because of you, a family in Gilgit-Baltistan will feel that someone stands with them. May Allah accept your giving.</p>

            <dl class="mt-6 grid grid-cols-[auto_1fr] gap-x-6 gap-y-1 text-ink-muted">
                <dt>Reference</dt><dd class="font-medium text-ink">{{ $donation->reference }}</dd>
            </dl>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="https://wa.me/?text={{ $share }}" rel="noopener" class="btn-primary">Share on WhatsApp</a>
                <a href="{{ route('projects.index') }}" class="btn-outline text-primary hover:bg-primary-tint">See what your gift builds</a>
            </div>
        </div>
    </section>
</x-layouts.app>
