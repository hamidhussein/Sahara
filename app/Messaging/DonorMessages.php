<?php

namespace App\Messaging;

use App\Models\Donation;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\URL;

/**
 * Messages SSWF sends donors. The wording comes from the editable templates
 * (MessageTemplates); this class fills in a donation's details.
 *
 * Today they go out by email (with the PDF attached) and by WhatsApp
 * click-to-chat, where Finance presses Send. When the WhatsApp Business API
 * and an SMS gateway are added, those channels send these same texts.
 */
final class DonorMessages
{
    /** Placeholder values for a donation. */
    public static function values(Donation $donation): array
    {
        $donation->loadMissing(['givingType', 'cause']);

        return [
            'name' => $donation->donor_name,
            'amount' => $donation->formattedAmount(),
            'type' => $donation->givingType->name,
            'cause' => $donation->causeLabel(),
            'reference' => $donation->reference,
            'date' => ($donation->paid_at ?? $donation->created_at)->format('j F Y'),
            'receipt_link' => self::receiptUrl($donation),
        ];
    }

    /** Confirmation with a secure link to the PDF receipt (WhatsApp now, API and SMS later). */
    public static function receiptConfirmation(Donation $donation): string
    {
        return MessageTemplates::render('whatsapp_receipt', self::values($donation));
    }

    /**
     * A permanent, signed link to the donor's PDF receipt. Only someone with
     * the exact link can open it, and only for confirmed donations.
     */
    public static function receiptUrl(Donation $donation): string
    {
        return URL::signedRoute('donations.receipt', $donation->reference);
    }

    /**
     * Opens WhatsApp (app or WhatsApp Web) on the donor's chat with the
     * confirmation ready to send. Null when the phone number cannot be used.
     */
    public static function whatsappLink(Donation $donation): ?string
    {
        $phone = PhoneNumber::international($donation->donor_phone);

        return $phone
            ? 'https://wa.me/'.$phone.'?text='.rawurlencode(self::receiptConfirmation($donation))
            : null;
    }
}
