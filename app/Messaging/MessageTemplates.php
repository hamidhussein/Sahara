<?php

namespace App\Messaging;

use App\Settings\MessageSettings;

/**
 * Editable messages to donors and people who contact SSWF.
 *
 * Each template has default wording, the placeholders it may use and the ones
 * it must keep. Staff edit the words in Admin → Settings → Donor messages;
 * an empty template falls back to the default, so nothing goes out blank.
 * The email layout, details table, PDF and registration footer stay fixed.
 */
final class MessageTemplates
{
    /** Placeholders available in donation messages, with what they become. */
    public const DONATION_PLACEHOLDERS = [
        'name' => 'Donor’s name',
        'amount' => 'Amount, e.g. Rs 5,000',
        'type' => 'Type of giving, e.g. Zakat',
        'cause' => 'Cause, or “Where it is needed most”',
        'reference' => 'Receipt number, e.g. SSWF-2026-000123',
        'date' => 'Date, e.g. 26 November 2026',
        'organization' => 'Sab Ka Sahara Welfare Foundation',
    ];

    /**
     * @return array<string, array{label: string, help: string, multiline: bool, placeholders: array<string, string>, required: list<string>, default: string}>
     */
    public static function definitions(): array
    {
        $donation = self::DONATION_PLACEHOLDERS;
        $withLink = $donation + ['receipt_link' => 'Secure link to the PDF receipt'];

        return [
            'whatsapp_receipt' => [
                'label' => 'WhatsApp receipt confirmation',
                'help' => 'Sent from the “Send receipt on WhatsApp” button after Finance confirms a payment. Plain text; *bold* works in WhatsApp.',
                'multiline' => true,
                'placeholders' => $withLink,
                'required' => ['reference', 'receipt_link'],
                'default' => implode("\n", [
                    'Dear {name},',
                    '',
                    'Thank you for your donation of {amount} as {type} for {cause}. It has been received and confirmed.',
                    '',
                    'Receipt number: {reference}',
                    'Download your receipt (PDF): {receipt_link}',
                    '',
                    'Because of you, a family in Gilgit-Baltistan will feel that someone stands with them.',
                    '',
                    '{organization}',
                ]),
            ],
            'receipt_email_subject' => [
                'label' => 'Receipt email: subject',
                'help' => 'Subject of the email with the PDF receipt, sent when a donation is confirmed.',
                'multiline' => false,
                'placeholders' => $donation,
                'required' => ['reference'],
                'default' => 'Thank you for your donation – Receipt #{reference}',
            ],
            'receipt_email_body' => [
                'label' => 'Receipt email: message',
                'help' => 'Shown above the receipt details table. The PDF receipt is attached automatically.',
                'multiline' => true,
                'placeholders' => $donation,
                'required' => [],
                'default' => implode("\n", [
                    'Dear {name},',
                    '',
                    'Thank you for your donation of {amount} as {type} for {cause} on {date}. Your receipt is attached.',
                    '',
                    'Your support helps us bring clean water, healthcare and food to families across Gilgit-Baltistan.',
                    '',
                    'With gratitude,',
                    '{organization}',
                ]),
            ],
            'received_email_subject' => [
                'label' => '“Receipt received” email: subject',
                'help' => 'Sent straight after a donor uploads a bank, JazzCash or Easypaisa receipt.',
                'multiline' => false,
                'placeholders' => $donation,
                'required' => [],
                'default' => 'We received your payment receipt',
            ],
            'received_email_body' => [
                'label' => '“Receipt received” email: message',
                'help' => 'Tell the donor what happens next and when.',
                'multiline' => true,
                'placeholders' => $donation,
                'required' => ['reference'],
                'default' => implode("\n", [
                    'Dear {name},',
                    '',
                    'Thank you. We have received your receipt for {amount} (reference {reference}) and will confirm your donation and send your official receipt within 24 hours.',
                    '',
                    'With gratitude,',
                    '{organization}',
                ]),
            ],
            'contact_reply_subject' => [
                'label' => 'Contact form reply: subject',
                'help' => 'Automatic reply to people who write through the Contact page and give an email address.',
                'multiline' => false,
                'placeholders' => ['name' => 'Their name', 'subject' => 'The subject they wrote', 'organization' => 'Sab Ka Sahara Welfare Foundation'],
                'required' => [],
                'default' => 'We received your message',
            ],
            'contact_reply_body' => [
                'label' => 'Contact form reply: message',
                'help' => 'Keep the emergency line: people sometimes write instead of calling.',
                'multiline' => true,
                'placeholders' => ['name' => 'Their name', 'subject' => 'The subject they wrote', 'organization' => 'Sab Ka Sahara Welfare Foundation'],
                'required' => [],
                'default' => implode("\n", [
                    'Dear {name},',
                    '',
                    'Thank you for writing to SSWF. We have received your message about “{subject}” and will reply as soon as we can.',
                    '',
                    'In an emergency, please call us instead of waiting for an email.',
                    '',
                    'With gratitude,',
                    '{organization}',
                ]),
            ],
        ];
    }

    public static function default(string $key): string
    {
        return self::definitions()[$key]['default'];
    }

    /** The saved wording, or the default when it is empty. */
    public static function text(string $key): string
    {
        $saved = app(MessageSettings::class)->{$key} ?? null;

        return filled($saved) ? $saved : self::default($key);
    }

    /**
     * Fill in the placeholders. Unknown placeholders are left as typed; the
     * editor's validation stops them being saved in the first place.
     *
     * @param  array<string, string|int|null>  $values
     */
    public static function render(string $key, array $values): string
    {
        $values['organization'] ??= config('sswf.name');

        return preg_replace_callback('/\{([a-z_]+)\}/', fn ($m) => array_key_exists($m[1], $values) ? (string) $values[$m[1]] : $m[0], self::text($key));
    }

    /**
     * Problems with a template, in plain words; empty when it is fine.
     *
     * @return list<string>
     */
    public static function problems(string $key, ?string $text): array
    {
        if (blank($text)) {
            return []; // empty means "use the default"
        }

        $definition = self::definitions()[$key];
        preg_match_all('/\{([^}]*)\}/', $text, $matches);
        $used = array_unique($matches[1]);

        $problems = [];
        foreach (array_diff($used, array_keys($definition['placeholders'])) as $unknown) {
            $problems[] = "{{$unknown}} is not a placeholder here. Use one of: ".self::list(array_keys($definition['placeholders'])).'.';
        }
        foreach (array_diff($definition['required'], $used) as $missing) {
            $problems[] = "Keep {{$missing}} in this message.";
        }

        return $problems;
    }

    /**
     * Plain text → Markdown for the email layout: HTML-escaped (donor names
     * cannot inject markup), blank lines start paragraphs, single line breaks kept.
     */
    public static function toMailMarkdown(string $text): string
    {
        $paragraphs = preg_split('/\R\s*\R/', trim($text)) ?: [];

        return collect($paragraphs)
            ->map(fn ($p) => collect(preg_split('/\R/', trim($p)))->map(fn ($line) => e(trim($line)))->implode("  \n"))
            ->implode("\n\n");
    }

    /** @param list<string> $names */
    public static function list(array $names): string
    {
        return implode(', ', array_map(fn ($n) => '{'.$n.'}', $names));
    }

    /** Sample values for previews in the admin. */
    public static function sampleValues(): array
    {
        return [
            'name' => 'Ali Raza',
            'amount' => 'Rs 5,000',
            'type' => 'Sadaqah',
            'cause' => 'Winter Package',
            'reference' => 'SSWF-'.now()->year.'-000123',
            'date' => now()->format('j F Y'),
            'receipt_link' => url('/receipts/SSWF-'.now()->year.'-000123?signature=…'),
            'subject' => 'Volunteering at the eye camp',
            'organization' => config('sswf.name'),
        ];
    }
}
