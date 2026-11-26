<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Bank details for transfers and the yearly Fitrana amount. Edited by Super Admin.
 */
class DonationSettings extends Settings
{
    public string $bank_name;

    /** To confirm after the name change. */
    public ?string $account_title;

    public string $account_number;

    public ?string $iban;

    public ?string $swift_code;

    /** Fitrana per person in rupees, announced before each Eid-ul-Fitr. */
    public ?int $fitrana_amount;

    /** Nisab in rupees for the Zakat calculator; changes with metal prices. */
    public ?int $nisab_amount;

    /** Raast ID (usually a mobile number) for instant bank transfers. */
    public ?string $raast_id;

    /** QR code issued by the bank (Raast QR), public disk path. */
    public ?string $bank_qr;

    // JazzCash and Easypaisa accounts donors send money to (manual mode, plan 1.2).
    public ?string $jazzcash_title;

    public ?string $jazzcash_number;

    public ?string $jazzcash_qr;

    public ?string $easypaisa_title;

    public ?string $easypaisa_number;

    public ?string $easypaisa_qr;

    public static function group(): string
    {
        return 'donation';
    }
}
