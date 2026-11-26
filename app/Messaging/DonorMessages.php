<?php

namespace App\Messaging;

use App\Models\Donation;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\URL;

/**
 * The words SSWF sends donors, in one place.
 *
 * Today they go out by email (with the PDF attached) and by WhatsApp
 * click-to-chat, where Finance presses Send. When the WhatsApp Business API
 * and an SMS gateway are added, those channels send these same texts, so a
 * donor reads the same thing whichever way it reaches them.
 */
final class DonorMessages
{
    /** Short confirmation with a secure link to the PDF receipt. */
    public static function receiptConfirmation(Donation $donation): string
    {
        $donation->loadMissing(['givingType', 'cause']);

        return implode("\n", [
            "Dear {$donation->donor_name},",
            '',
            "Thank you for your donation of {$donation->formattedAmount()} as {$donation->givingType->name} for {$donation->causeLabel()}. It has been received and confirmed.",
            '',
            "Receipt number: {$donation->reference}",
            'Download your receipt (PDF): '.self::receiptUrl($donation),
            '',
            'Because of you, a family in Gilgit-Baltistan will feel that someone stands with them.',
            '',
            'Sab Ka Sahara Welfare Foundation',
        ]);
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
