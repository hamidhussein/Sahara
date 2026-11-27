<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasWebpImages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

class GalleryAlbum extends Model implements HasMedia
{
    use HasSlug, HasWebpImages, RecordsActivity;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'taken_on' => 'date',
            'videos' => 'array',
            'is_published' => 'boolean',
        ];
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos');
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->orderBy('sort_order')->orderByDesc('taken_on');
    }

    /**
     * Videos with a recognised YouTube ID, ready for thumbnails and the player.
     *
     * @return list<array{id: string, title: string}>
     */
    public function youtubeVideos(): array
    {
        return collect($this->videos ?? [])
            ->map(fn ($v) => ['id' => static::youtubeId($v['url'] ?? ''), 'title' => trim($v['title'] ?? '') ?: $this->title])
            ->filter(fn ($v) => $v['id'])
            ->values()
            ->all();
    }

    public static function youtubeId(string $url): ?string
    {
        $pattern = '~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~';

        return preg_match($pattern, $url, $m) ? $m[1] : null;
    }

    /** An album is shown only when there is something to see. */
    public function hasContent(): bool
    {
        return $this->getMedia('photos')->isNotEmpty() || $this->youtubeVideos() !== [];
    }

    public function coverUrl(): ?string
    {
        if ($photo = $this->imageUrl('photos', 'card')) {
            return $photo;
        }

        $video = $this->youtubeVideos()[0] ?? null;

        return $video ? "https://i.ytimg.com/vi/{$video['id']}/hqdefault.jpg" : null;
    }
}
