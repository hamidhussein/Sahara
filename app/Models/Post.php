<?php

namespace App\Models;

use App\Enums\ContentLanguage;
use App\Enums\PostCategory;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasWebpImages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;

class Post extends Model implements HasMedia
{
    use HasSlug, HasWebpImages;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'category' => PostCategory::class,
            'language' => ContentLanguage::class,
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** Published means a publish date that has arrived; empty means draft. */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function excerptText(): string
    {
        return $this->excerpt ?: Str::limit(trim(strip_tags((string) $this->body)), 160);
    }
}
