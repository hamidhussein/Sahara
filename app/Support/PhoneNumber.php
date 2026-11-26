<?php

namespace App\Support;

/**
 * Turns the phone numbers donors type ("0300 1234567", "+92 300-1234567",
 * "0092300...") into the international digits WhatsApp and SMS gateways need
 * ("923001234567"). Numbers without a country code are taken as Pakistani.
 */
final class PhoneNumber
{
    public static function international(?string $number): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $number);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = '92'.substr($digits, 1); // local Pakistani format, e.g. 0300…
        }

        // Pakistani mobiles are 92 + 10 digits; other countries vary, but under 10 digits is never valid.
        return strlen($digits) >= 10 && strlen($digits) <= 15 ? $digits : null;
    }
}
