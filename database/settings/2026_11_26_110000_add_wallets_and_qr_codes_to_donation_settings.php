<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/*
 * JazzCash and Easypaisa accounts that donors send money to, and the QR codes
 * issued by the bank and wallet providers. All empty until Finance enters the
 * real details: a method only appears on the Donate page once it has them.
 * (Local and test copies get clearly marked samples from SamplePaymentDetailsSeeder.)
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('donation.jazzcash_title', null);
        $this->migrator->add('donation.jazzcash_number', null);
        $this->migrator->add('donation.jazzcash_qr', null);

        $this->migrator->add('donation.easypaisa_title', null);
        $this->migrator->add('donation.easypaisa_number', null);
        $this->migrator->add('donation.easypaisa_qr', null);

        $this->migrator->add('donation.raast_id', null);
        $this->migrator->add('donation.bank_qr', null);
    }
};
