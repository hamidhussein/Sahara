<x-mail::message>
{!! $body !!}

<x-mail::table>
| | |
|:--|:--|
| Receipt number | {{ $donation->reference }} |
| Amount | {{ $donation->formattedAmount() }} |
| Type of giving | {{ $donation->givingType->name }} |
| Cause | {{ $donation->causeLabel() }} |
| Paid by | {{ $donation->payment_method->getLabel() }} |
</x-mail::table>

<x-mail::button :url="route('projects.index')" color="success">
See our latest projects
</x-mail::button>

<x-slot:subcopy>
Reg. No. {{ $site->registration_number }} · {{ $site->office_phone }} · {{ $site->email }}
</x-slot:subcopy>
</x-mail::message>
