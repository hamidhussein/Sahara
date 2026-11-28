<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('home.about_video_url', 'https://www.youtube.com/watch?v=7kD_GSO9bW4');
        $this->migrator->add('home.about_video_title', 'Subka Sahara Welfare Foundation story');
    }
};
