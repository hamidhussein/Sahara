<?php

namespace App\Mail;

use App\Filament\Resources\Donations\DonationResource;
use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * To Finance when a donor uploads a bank, JazzCash or Easypaisa receipt.
 * The receipt itself stays behind the admin sign-in; only a link is sent.
 */
class TransferReceiptToCheck extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Donation $donation)
    {
        // Send only once the donation and its receipt are saved.
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Receipt to check: {$this->donation->formattedAmount()} by {$this->donation->payment_method->getLabel()} ({$this->donation->reference})",
            replyTo: $this->donation->donor_email ? [new Address($this->donation->donor_email, $this->donation->donor_name)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.transfer-receipt-to-check', with: [
            'adminUrl' => DonationResource::getUrl('view', ['record' => $this->donation], panel: 'admin'),
        ]);
    }
}
