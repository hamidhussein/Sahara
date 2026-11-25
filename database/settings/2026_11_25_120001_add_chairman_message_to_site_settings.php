<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // The plan's draft is marked "[Chairman to review]", so the Leadership
        // page shows no message until the approved text is entered in Settings.
        $this->migrator->add('site.chairman_message', null);
    }
};
