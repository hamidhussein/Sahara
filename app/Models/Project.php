<?php

namespace App\Models;

use App\Enums\ContentLanguage;
use App\Enums\ProjectStatus;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasWebpImages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;

class Project extends Model implements HasMedia
{
    use HasSlug, HasWebpImages;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'language' => ContentLanguage::class,
            'started_on' => 'date',
            'completed_on' => 'date',
            'is_featured' => 'boolean',
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
        $this->addMediaCollection('before')->singleFile();
        $this->addMediaCollection('after')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(ProjectUpdate::class)->orderBy('happened_on');
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /** "Miachar, Nagar", or whichever part is known. */
    public function place(): ?string
    {
        return collect([$this->village, $this->district])->filter()->implode(', ') ?: null;
    }
}
