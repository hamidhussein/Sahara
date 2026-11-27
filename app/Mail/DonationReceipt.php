<?php

namespace App\Mail;

use App\Messaging\DonorMessages;
use App\Messaging\MessageTemplates;
use App\Models\Donation;
use App\Settings\SiteSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Plan 1.1, Emails: "Thank you for your donation – Receipt #[number]". */
class DonationReceipt extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Donation $donation) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: MessageTemplates::render('receipt_email_subject', DonorMessages::values($this->donation)));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.donation-receipt', with: [
            'site' => app(SiteSettings::class),
            'body' => MessageTemplates::toMailMarkdown(MessageTemplates::render('receipt_email_body', DonorMessages::values($this->donation))),
        ]);
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => static::pdf($this->donation)->output(), "SSWF-receipt-{$this->donation->reference}.pdf")
                ->withMime('application/pdf'),
        ];
    }

    public static function pdf(Donation $donation): \Barryvdh\DomPDF\PDF
    {
        $donation->loadMissing(['givingType', 'cause']);

        return Pdf::loadView('pdf.donation-receipt', [
            'donation' => $donation,
            'site' => app(SiteSettings::class),
        ])->setPaper('a5');
    }
}
