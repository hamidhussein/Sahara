<x-mail::message>
Dear {{ $donation->donor_name }},

Thank you. We have received your receipt for **{{ $donation->formattedAmount() }}** (reference {{ $donation->reference }}) and will confirm your donation and send your official receipt within 24 hours.

With gratitude,<br>
Sab Ka Sahara Welfare Foundation
</x-mail::message>
