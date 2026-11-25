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

    public static function group(): string
    {
        return 'donation';
    }
}
