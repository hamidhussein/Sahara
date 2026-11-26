<?php

namespace App\Mail;

use App\Messaging\MessageTemplates;
use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Automatic reply to the Contact form (plan 1.1, Emails). Wording is editable. */
class ContactAutoReply extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    protected function values(): array
    {
        return ['name' => $this->contactMessage->name, 'subject' => $this->contactMessage->subject];
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: MessageTemplates::render('contact_reply_subject', $this->values()));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.contact-auto-reply', with: [
            'body' => MessageTemplates::toMailMarkdown(MessageTemplates::render('contact_reply_body', $this->values())),
        ]);
    }
}
