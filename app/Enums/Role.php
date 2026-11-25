<?php

namespace App\Enums;

/**
 * Admin roles from plan 1.2. Services is used from Phase 2.
 */
enum Role: string
{
    case SuperAdmin = 'Super Admin';
    case Editor = 'Editor';
    case Finance = 'Finance';
    case Services = 'Services';

    /** @return list<string> */
    public static function names(): array
    {
        return array_column(self::cases(), 'value');
    }
}
