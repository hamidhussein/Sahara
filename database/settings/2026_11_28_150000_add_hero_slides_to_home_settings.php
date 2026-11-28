<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Hero carousel. Starts with three slides built from facts already on the site;
 * each uses a built-in photo until staff upload their own in the admin.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('home.hero_slides', [
            [
                'heading' => 'Support for everyone who has none.',
                'text' => 'From clean water in Nagar to free eye surgeries and food for families in need, Subka Sahara Welfare Foundation stands with the people of Gilgit-Baltistan, day and night.',
                'image' => null,
                'image_alt' => 'Subka Sahara volunteers delivering clean drinking water in Danyor',
                'preset' => 'danyor-water',
            ],
            [
                'heading' => 'Clean, safe water for every home.',
                'text' => 'Thirteen water filtration plants across Nagar and Gilgit give families safe drinking water every day.',
                'image' => null,
                'image_alt' => 'A woman filling a cup at the Miachar water filtration plant',
                'preset' => 'water-plant',
            ],
            [
                'heading' => 'Free eye care for 3,000 patients.',
                'text' => 'Our 2026 eye camp examined 3,000 patients and gave more than 300 free operations, with follow-up care and medicines.',
                'image' => null,
                'image_alt' => 'A doctor examining a patient’s eyes at the free eye camp',
                'preset' => 'eye-camp',
            ],
        ]);
        $this->migrator->add('home.hero_autoplay', true);
        $this->migrator->add('home.hero_interval', 7);
    }
};
