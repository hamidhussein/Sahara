<?php

namespace App\Models;

use App\Enums\TeamGroup;
use App\Models\Concerns\HasWebpImages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;

class TeamMember extends Model implements HasMedia
{
    use HasWebpImages;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'group' => TeamGroup::class,
            'is_published' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->orderBy('sort_order')->orderBy('name');
    }

    /** "AH" for Arif Hussain; skips honorifics so initials stay meaningful. */
    public function initials(): string
    {
        $words = collect(explode(' ', $this->name))
            ->reject(fn ($w) => in_array(Str::lower(rtrim($w, '.')), ['haji', 'agha', 'sheikh', 'syed', 'dr', 'prof', 'mr'], true))
            ->values();

        return Str::upper(collect([$words->first(), $words->last()])->filter()->unique()->map(fn ($w) => mb_substr($w, 0, 1))->implode(''));
    }
}
