<?php

namespace App\Support;

/**
 * Partly hides contact details on pages a donor might forward or screenshot:
 * enough for them to recognise their own details, not enough for anyone else.
 */
final class Mask
{
    /** "ayesha.khan@gmail.com" → "a•••@gmail.com" */
    public static function email(?string $email): ?string
    {
        if (! $email || ! str_contains($email, '@')) {
            return $email;
        }

        [$name, $domain] = explode('@', $email, 2);

        return mb_substr($name, 0, 1).'•••@'.$domain;
    }

    /** "0312 7654321" → "0312 •••4321" (first four and last four digits) */
    public static function phone(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if (strlen($digits) < 8) {
            return $phone ? '•••' : null;
        }

        return substr($digits, 0, 4).' •••'.substr($digits, -4);
    }
}
