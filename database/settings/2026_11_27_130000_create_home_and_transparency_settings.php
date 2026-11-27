<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/** Starts with the wording the site already shows, so nothing changes until someone edits it. */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('home.hero_heading', 'Support for everyone who has none.');
        $this->migrator->add('home.hero_text', 'From clean water in Nagar to free eye surgeries and food for families in need, Subka Sahara Welfare Foundation stands with the people of Gilgit-Baltistan, day and night.');
        $this->migrator->add('home.hero_image', null);
        $this->migrator->add('home.hero_image_alt', 'Subka Sahara volunteers delivering clean drinking water in Danyor');
        $this->migrator->add('home.tiers', [
            ['amount' => 10000, 'text' => 'gives one family a Ramadan ration package', 'cause' => 'ramadan-package'],
            ['amount' => 50000, 'text' => 'helps a talented student through a year of medical or engineering study (interest-free loan)', 'cause' => 'education'],
            ['amount' => null, 'text' => 'brings the Sahara Complex, and its low-cost laboratory, a step closer', 'cause' => 'sahara-complex'],
        ]);
        $this->migrator->add('home.volunteer_heading', 'Be someone’s Sahara.');
        $this->migrator->add('home.volunteer_text', 'Our volunteers carried water to thirsty homes in Danyor for 28 days and served at every camp and procession. Join them.');

        $this->migrator->add('transparency.funding', [
            'Monthly fees from registered members',
            'Donation boxes',
            'General donations',
            'Sadaqat, Zakat and Khums',
            'Rent from the shops in our building',
        ]);
        $this->migrator->add('transparency.assets', [
            'A 5-marla building (basement hall and three rented shops) for the Sahara Complex',
            '3 ambulances',
            'Office furniture and equipment',
        ]);
        $this->migrator->add('transparency.policy', [
            'Zakat, Khums, Fitrana, Fidya and Kaffarah are each kept in a separate fund and recorded separately.',
            'Zakat and Fitrana go only to eligible (mustahiq) individuals and families.',
            'Khums is used only for permitted purposes, under the permission (ijazah) of the Marja’s representative, as that permission directs.',
            'Beneficiaries are verified through surveys by our volunteers and local elders.',
        ]);
    }
};
