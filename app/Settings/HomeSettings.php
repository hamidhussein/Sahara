<?php

namespace App\Settings;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

/** Home page text that changes with campaigns (Admin → Settings → Home page). */
class HomeSettings extends Settings
{
    public string $hero_heading;

    public string $hero_text;

    /** Public disk path; empty uses the built-in Danyor water photo. */
    public ?string $hero_image;

    public string $hero_image_alt;

    /**
     * "How your donation helps": up to three items, each with amount (rupees,
     * or empty for "any amount"), text and cause (a cause slug, or empty).
     * Deliberately has no docblock type tag: the settings package reads those
     * and cannot resolve array shapes, so it is stored as a plain array.
     */
    public array $tiers;

    public string $volunteer_heading;

    public string $volunteer_text;

    public static function group(): string
    {
        return 'home';
    }

    public function heroImageUrl(): string
    {
        return $this->hero_image && Storage::disk('public')->exists($this->hero_image)
            ? Storage::disk('public')->url($this->hero_image)
            : asset('images/hero-danyor-water.jpg');
    }
}
