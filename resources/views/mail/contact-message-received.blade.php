<x-mail::message>
New message from the website.

<x-mail::table>
| | |
|:--|:--|
| From | {{ $contactMessage->name }} |
| Email | {{ $contactMessage->email ?? '–' }} |
| Phone | {{ $contactMessage->phone ?? '–' }} |
| Subject | {{ $contactMessage->subject }} |
</x-mail::table>

<x-mail::panel>
{{ $contactMessage->message }}
</x-mail::panel>

@if ($contactMessage->email)
Reply to this email to answer {{ $contactMessage->name }} directly.
@else
{{ $contactMessage->name }} gave a phone number only: {{ $contactMessage->phone }}.
@endif

<x-mail::button :url="url('/admin/contact-messages/'.$contactMessage->id)" color="success">
Open in the admin
</x-mail::button>
</x-mail::message>
