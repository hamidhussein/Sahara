<x-mail::message>
Dear {{ $donation->donor_name }},

Thank you for your donation of **{{ $donation->formattedAmount() }}** as {{ $donation->givingType->name }} for {{ $donation->causeLabel() }} on {{ $donation->paid_at->format('j F Y') }}. Your receipt is attached.

<x-mail::table>
| | |
|:--|:--|
| Receipt number | {{ $donation->reference }} |
| Amount | {{ $donation->formattedAmount() }} |
| Type of giving | {{ $donation->givingType->name }} |
| Cause | {{ $donation->causeLabel() }} |
| Paid by | {{ $donation->payment_method->getLabel() }} |
</x-mail::table>

Your support helps us bring clean water, healthcare and food to families across Gilgit-Baltistan.

<x-mail::button :url="route('projects.index')" color="success">
See our latest projects
</x-mail::button>

With gratitude,<br>
Sab Ka Sahara Welfare Foundation

<x-slot:subcopy>
Reg. No. {{ $site->registration_number }} · {{ $site->office_phone }} · {{ $site->email }}
</x-slot:subcopy>
</x-mail::message>
