<x-mail::message>
A donor has uploaded a payment receipt. Please find the payment in the account statement, then confirm it in the admin so the donor gets their receipt.

<x-mail::table>
| | |
|:--|:--|
| Amount | **{{ $donation->formattedAmount() }}** |
| Paid by | {{ $donation->payment_method->getLabel() }} |
| Reference | {{ $donation->reference }} |
| For | {{ $donation->givingType?->name }} · {{ $donation->causeLabel() }} |
| Donor | {{ $donation->donor_name }}{{ $donation->is_anonymous ? ' (wants to stay anonymous)' : '' }} |
| Phone | {{ $donation->donor_phone ?: '–' }} |
| Email | {{ $donation->donor_email ?: '–' }} |
| Received | {{ $donation->created_at->timezone('Asia/Karachi')->format('j M Y, g:i a') }} |
</x-mail::table>

@if ($donation->needs_eligibility_review)
<x-mail::panel>
**Check eligibility first.** This is {{ $donation->givingType?->name }} for a cause not yet marked as eligible for it. Contact the donor before using the money.
</x-mail::panel>
@endif

<x-mail::button :url="$adminUrl" color="success">
Check the receipt in the admin
</x-mail::button>

The receipt image is not attached to this email for privacy. Sign in to the admin to see it.
</x-mail::message>
