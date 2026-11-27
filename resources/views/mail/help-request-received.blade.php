<x-mail::message>
Someone has asked for help on the website. Please call them as soon as you can.

<x-mail::table>
| | |
|:--|:--|
| Request | **{{ $helpRequest->reference }}** |
| Type | {{ $helpRequest->type->getLabel() }} |
| Name | {{ $helpRequest->name }} |
| Mobile | {{ $helpRequest->phone }} |
| Place | {{ $helpRequest->place() ?? '–' }} |
| Person in need | {{ $helpRequest->person_in_need ?: 'The person asking' }} |
| Received | {{ $helpRequest->created_at->timezone('Asia/Karachi')->format('j M Y, g:i a') }} |
</x-mail::table>

<x-mail::panel>
{{ $helpRequest->details }}
</x-mail::panel>

@if ($helpRequest->hasMedia('document'))
They attached a document. Open the request in the admin to see it.
@endif

<x-mail::button :url="$adminUrl" color="success">
Open the request
</x-mail::button>
</x-mail::message>
