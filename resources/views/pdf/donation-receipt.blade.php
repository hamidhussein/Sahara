<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $donation->reference }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2532; }
        .head { border-bottom: 3px solid #0d2d5e; padding-bottom: 10px; margin-bottom: 16px; }
        .org { font-size: 16px; font-weight: bold; color: #0d2d5e; }
        .muted { color: #565d6b; }
        h1 { font-size: 14px; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px 0; border-bottom: 1px solid #e2e6ec; vertical-align: top; }
        td.label { width: 40%; color: #565d6b; }
        .amount { font-size: 18px; font-weight: bold; color: #0d2d5e; }
        .foot { margin-top: 22px; font-size: 9.5px; color: #565d6b; }
    </style>
</head>
<body>
    <div class="head">
        <div class="org">Subka Sahara Welfare Foundation</div>
        <div class="muted">{{ $site->address }}</div>
        <div class="muted">{{ $site->office_phone }} · {{ $site->email }}</div>
    </div>

    <h1>Donation receipt</h1>

    <table>
        <tr><td class="label">Receipt number</td><td><strong>{{ $donation->reference }}</strong></td></tr>
        <tr><td class="label">Date</td><td>{{ $donation->paid_at->format('j F Y') }}</td></tr>
        <tr><td class="label">Received from</td><td>{{ $donation->donor_name }}</td></tr>
        <tr><td class="label">Amount</td><td class="amount">{{ $donation->formattedAmount() }}</td></tr>
        <tr><td class="label">Type of giving</td><td>{{ $donation->givingType->name }}</td></tr>
        <tr><td class="label">Cause</td><td>{{ $donation->causeLabel() }}</td></tr>
        <tr><td class="label">Payment method</td><td>{{ $donation->payment_method->getLabel() }}</td></tr>
        @if ($donation->gateway_transaction_id)
            <tr><td class="label">Transaction ID</td><td>{{ $donation->gateway_transaction_id }}</td></tr>
        @endif
    </table>

    <p style="margin-top: 16px;">Thank you. Because of you, a family in Gilgit-Baltistan will feel that someone stands with them.</p>

    <div class="foot">
        Registered under the Voluntary Social Welfare Agencies Ordinance 1961, Reg. No. {{ $site->registration_number }}.
        Zakat and Khums are kept in separate funds and used only for eligible purposes.
        This receipt was generated electronically.
    </div>
</body>
</html>
