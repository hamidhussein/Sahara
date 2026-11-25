<?php

namespace App\Models;

use App\Enums\DonationStatus;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasWebpImages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;

/**
 * What a donation is for (Clean Water, Winter Package, Sahara Complex, ...).
 * A cause can carry a goal and appear on the home page as an appeal.
 */
class Cause extends Model implements HasMedia
{
    use HasSlug, HasWebpImages;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_appeal' => 'boolean',
            'is_active' => 'boolean',
            'goal_amount' => 'integer',
        ];
    }

    protected function slugSource(): string
    {
        return 'name';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /** Restricted funds (Zakat, Khums, ...) this cause may receive. */
    public function eligibleGivingTypes(): BelongsToMany
    {
        return $this->belongsToMany(GivingType::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    /** Total confirmed (paid) donations to this cause, in rupees. */
    public function raisedAmount(): int
    {
        return (int) ($this->paid_total ?? $this->donations()->where('status', DonationStatus::Paid)->sum('amount'));
    }

    /** 0–100, or null when there is no goal. */
    public function progressPercent(): ?int
    {
        if (! $this->goal_amount) {
            return null;
        }

        return (int) min(100, floor($this->raisedAmount() / $this->goal_amount * 100));
    }

    /** Adds paid_total so a list of causes can show progress without one query each. */
    public function scopeWithPaidTotal(Builder $query): void
    {
        $query->withSum(['donations as paid_total' => fn ($q) => $q->where('status', DonationStatus::Paid)], 'amount');
    }
}
