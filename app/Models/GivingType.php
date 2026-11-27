<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A type of giving, each kept as its own fund (plan 1.2).
 */
class GivingType extends Model
{
    use HasSlug, RecordsActivity;

    protected function activityArea(): string
    {
        return 'donations';
    }

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_restricted' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected function slugSource(): string
    {
        return 'name';
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /** Causes Finance has marked as eligible for this restricted fund. */
    public function eligibleCauses(): BelongsToMany
    {
        return $this->belongsToMany(Cause::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    public function acceptsCause(?Cause $cause): bool
    {
        if (! $this->is_restricted || ! $cause) {
            return true;
        }

        return $cause->eligibleGivingTypes()->whereKey($this->getKey())->exists();
    }
}
