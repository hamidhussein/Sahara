<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/*
 * Starting values from the SSWF Website Master Plan. Anything the plan lists
 * under "Facts to confirm" starts empty (null) and is filled in from
 * Admin → Settings once confirmed, never guessed here.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        // Contact and registration
        $this->migrator->add('site.office_phone', '+92 5811 453014');
        $this->migrator->add('site.ambulance_phone', null);
        $this->migrator->add('site.whatsapp_number', null);
        $this->migrator->add('site.email', 'info@subkasahara.org');
        $this->migrator->add('site.address', 'G-18, Hussainia Complex, Khazana Road, Gilgit, Gilgit-Baltistan');
        $this->migrator->add('site.registration_number', 'J-6 (16)/203/2012');
        $this->migrator->add('site.office_hours', null);
        $this->migrator->add('site.facebook_url', 'https://www.facebook.com/sswfgilgit/');
        $this->migrator->add('site.instagram_url', null);
        $this->migrator->add('site.youtube_url', null);
        $this->migrator->add('site.tiktok_url', null);

        // Bank transfer details
        $this->migrator->add('donation.bank_name', 'Bank Al-Habib, Main Branch Gilgit');
        $this->migrator->add('donation.account_title', null);
        $this->migrator->add('donation.account_number', '2010-0081-002350-01-8');
        $this->migrator->add('donation.iban', null);
        $this->migrator->add('donation.swift_code', null);
        $this->migrator->add('donation.fitrana_amount', null);

        // Home page impact counters
        $this->migrator->add('impact.founded_year', 2006);
        $this->migrator->add('impact.water_plants', 13);
        $this->migrator->add('impact.report_year', 2026);
        $this->migrator->add('impact.patients_examined', 3000);
        $this->migrator->add('impact.eye_operations', 300);
        $this->migrator->add('impact.ramadan_families', 1067);
    }
};
