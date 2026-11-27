<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Where "receipt to check" alerts go. Empty: every Finance user.
        $this->migrator->add('donation.finance_email', null);
    }
};
