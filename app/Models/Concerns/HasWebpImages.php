<?php

namespace App\Models\Concerns;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * WebP images in several sizes (plan 1.2: "WebP in several sizes").
 * Conversions are queued; run `php artisan queue:work` to generate them.
 */
trait HasWebpImages
{
    use InteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->format('webp')->fit(Fit::Max, 400, 400)->quality(75);
        $this->addMediaConversion('card')->format('webp')->fit(Fit::Max, 800, 800)->quality(75);
        $this->addMediaConversion('hero')->format('webp')->fit(Fit::Max, 1600, 1600)->quality(72);
    }

    /** URL of the first image in a collection in the given size, falling back to the original. */
    public function imageUrl(string $collection = 'cover', string $size = 'card'): ?string
    {
        $media = $this->getFirstMedia($collection);

        if (! $media) {
            return null;
        }

        return $media->hasGeneratedConversion($size) ? $media->getUrl($size) : $media->getUrl();
    }
}
