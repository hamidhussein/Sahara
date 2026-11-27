<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Fills `slug` from the title field when left empty and keeps it unique.
 * Urdu titles are transliterated to Latin letters; if nothing usable is left,
 * a short random slug is used that the editor can change.
 */
trait HasSlug
{
    abstract protected function slugSource(): string;

    protected static function bootHasSlug(): void
    {
        static::saving(function ($model) {
            $base = Str::slug((string) ($model->slug ?: $model->{$model->slugSource()}))
                ?: strtolower(class_basename($model)).'-'.Str::lower(Str::random(6));

            $slug = $base;
            $i = 2;
            while (static::where('slug', $slug)->whereKeyNot($model->getKey())->exists()) {
                $slug = $base.'-'.$i++;
            }

            $model->slug = $slug;
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
