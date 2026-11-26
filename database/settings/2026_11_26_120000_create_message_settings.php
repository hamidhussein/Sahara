<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/*
 * Editable message templates start empty, which means "use the default
 * wording" (App\Messaging\MessageTemplates). Nothing changes for donors until
 * someone edits a template in Admin → Settings → Donor messages.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach ([
            'whatsapp_receipt',
            'receipt_email_subject', 'receipt_email_body',
            'received_email_subject', 'received_email_body',
            'contact_reply_subject', 'contact_reply_body',
        ] as $key) {
            $this->migrator->add("messages.{$key}", null);
        }
    }
};
