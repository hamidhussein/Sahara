<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use App\Models\Concerns\HasWebpImages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

class Partner extends Model implements HasMedia
{
    use HasWebpImages, RecordsActivity;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->orderBy('sort_order')->orderBy('name');
    }
}
