<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // SEO defaults (plan 1.2 Settings): used when a page has no description or photo of its own.
        $this->migrator->add('site.seo_description', 'Since 2006, Sab Ka Sahara Welfare Foundation has served Gilgit-Baltistan with clean water, free health camps, ambulances, education and food for families.');
        $this->migrator->add('site.share_image', null); // path on the public disk; falls back to the hero photo
    }
};
