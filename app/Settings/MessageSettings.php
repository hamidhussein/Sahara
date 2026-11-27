<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Edited wording of donor and contact messages (see App\Messaging\MessageTemplates).
 * Null or empty means "use the default wording".
 */
class MessageSettings extends Settings
{
    public ?string $whatsapp_receipt;

    public ?string $receipt_email_subject;

    public ?string $receipt_email_body;

    public ?string $received_email_subject;

    public ?string $received_email_body;

    public ?string $contact_reply_subject;

    public ?string $contact_reply_body;

    public static function group(): string
    {
        return 'messages';
    }
}
