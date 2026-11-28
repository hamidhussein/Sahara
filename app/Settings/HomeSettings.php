<?php

namespace App\Settings;

use App\Models\GalleryAlbum;
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

    public ?string $about_video_url;

    public ?string $about_video_title;

    /**
     * Hero slides: up to five, each with heading, text, image (public disk path or
     * empty), image_alt and preset (a built-in photo used while image is empty).
     * Empty list: the single hero_* fields above are shown. Slide 1 is copied into
     * hero_heading/hero_text/hero_image/hero_image_alt on save, for share previews.
     * No docblock type tag on purpose (see tiers).
     */
    public array $hero_slides;

    public bool $hero_autoplay;

    /** Seconds each slide stays before the next one, 4–15. */
    public int $hero_interval;

    /** Built-in background photos: preset key → widths of public/images/hero-{key}-{width}.webp */
    public const HERO_PRESETS = [
        'danyor-water' => ['label' => 'Water delivery in Danyor', 'widths' => [640, 1280, 1376]],
        'water-plant' => ['label' => 'Miachar water plant', 'widths' => [640, 1264]],
        'eye-camp' => ['label' => 'Free eye camp', 'widths' => [640, 1264]],
    ];

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

    public function aboutVideoId(): ?string
    {
        return $this->about_video_url
            ? GalleryAlbum::youtubeId($this->about_video_url)
            : null;
    }

    /**
     * WebP sizes of the built-in photo (45–182 KB instead of the 1 MB JPG),
     * or null for an uploaded photo, which is used as it is.
     */
    public function heroSrcset(): ?string
    {
        if ($this->hero_image && Storage::disk('public')->exists($this->hero_image)) {
            return null;
        }

        return static::builtInSrcset();
    }

    public static function builtInSrcset(): string
    {
        return collect([640, 1280, 1376])
            ->map(fn ($width) => asset("images/hero-danyor-water-{$width}.webp")." {$width}w")
            ->implode(', ');
    }

    /**
     * Slides ready for the hero: heading, text, alt, src and srcset (null for uploads).
     *
     * @return list<array{heading: string, text: string, alt: string, src: string, srcset: ?string}>
     */
    public function heroSlides(): array
    {
        $slides = collect($this->hero_slides)->values()->filter(fn ($slide) => filled($slide['heading'] ?? null));

        if ($slides->isEmpty()) {
            $slides = collect([[
                'heading' => $this->hero_heading,
                'text' => $this->hero_text,
                'image' => $this->hero_image,
                'image_alt' => $this->hero_image_alt,
                'preset' => 'danyor-water',
            ]]);
        }

        return $slides->map(function ($slide) {
            $image = $slide['image'] ?? null;
            $uploaded = $image && Storage::disk('public')->exists($image);
            $preset = array_key_exists($slide['preset'] ?? '', self::HERO_PRESETS) ? $slide['preset'] : 'danyor-water';
            $widths = self::HERO_PRESETS[$preset]['widths'];

            return [
                'heading' => (string) $slide['heading'],
                'text' => (string) ($slide['text'] ?? ''),
                'alt' => (string) ($slide['image_alt'] ?? ''),
                'src' => $uploaded ? Storage::disk('public')->url($image) : asset("images/hero-{$preset}-{$widths[0]}.webp"),
                'srcset' => $uploaded ? null : collect($widths)->map(fn ($w) => asset("images/hero-{$preset}-{$w}.webp")." {$w}w")->implode(', '),
            ];
        })->values()->all();
    }

    public function heroImageUrl(): string
    {
        return $this->hero_image && Storage::disk('public')->exists($this->hero_image)
            ? Storage::disk('public')->url($this->hero_image)
            : asset('images/hero-danyor-water.jpg');
    }
}
