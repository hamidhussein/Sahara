<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use App\Enums\ContentLanguage;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasWebpImages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;

class Program extends Model implements HasMedia
{
    use HasSlug, HasWebpImages, RecordsActivity;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'language' => ContentLanguage::class,
            'is_published' => 'boolean',
        ];
    }

    protected function slugSource(): string
    {
        return 'name';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }
}
