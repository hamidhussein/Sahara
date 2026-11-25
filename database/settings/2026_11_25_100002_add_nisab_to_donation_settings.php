<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Nisab in rupees for the Zakat calculator. Empty until Finance sets it;
        // it changes with the price of silver and gold.
        $this->migrator->add('donation.nisab_amount', null);
    }
};
