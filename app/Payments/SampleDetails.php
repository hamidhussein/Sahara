<?php

namespace App\Payments;

/**
 * Sample wallet details for local and test copies only.
 *
 * They exist so the Donate page can be tried end to end before SSWF's real
 * JazzCash and Easypaisa accounts are entered. The live site treats them as
 * missing, so a donor can never be shown a made-up account to send money to.
 */
final class SampleDetails
{
    public const TITLE = 'SAMPLE ACCOUNT – replace before launch';

    public const JAZZCASH_NUMBER = '0300 0000000';

    public const EASYPAISA_NUMBER = '0345 0000000';

    public const RAAST_ID = '0300 0000000';

    /** Sample QR images live here on the public disk; the live site never shows them. */
    public const QR_DIRECTORY = 'samples';

    public static function isSample(?string $value): bool
    {
        if (blank($value)) {
            return false;
        }

        return in_array(trim($value), [self::TITLE, self::JAZZCASH_NUMBER, self::EASYPAISA_NUMBER, self::RAAST_ID], true)
            || str_starts_with(trim($value), self::QR_DIRECTORY.'/');
    }

    /** Samples are allowed everywhere except the live site. */
    public static function allowed(): bool
    {
        return config('app.env') !== 'production';
    }

    /** A value is usable if it is filled in and is either real or a sample on a non-live copy. */
    public static function usable(?string $value): bool
    {
        return filled($value) && (! self::isSample($value) || self::allowed());
    }
}
