<?php

namespace App\Mail;

use App\Messaging\DonorMessages;
use App\Messaging\MessageTemplates;
use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Sent when a donor uploads a bank, JazzCash or Easypaisa receipt. Wording is editable. */
class TransferReceiptReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Donation $donation) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: MessageTemplates::render('received_email_subject', DonorMessages::values($this->donation)));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.transfer-receipt-received', with: [
            'body' => MessageTemplates::toMailMarkdown(MessageTemplates::render('received_email_body', DonorMessages::values($this->donation))),
        ]);
    }
}
