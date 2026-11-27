<?php

namespace App\Models;

use App\Enums\HelpStatus;
use App\Enums\HelpType;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/** A request from the Get Help page. Private: only the Services team sees it. */
class HelpRequest extends Model implements HasMedia
{
    use InteractsWithMedia, RecordsActivity;

    protected $guarded = ['id'];

    protected $hidden = ['ip_address'];

    /** People ask for help on the website; the log records how staff handle it, never what they wrote. */
    protected static array $recordEvents = ['updated', 'deleted'];

    protected function casts(): array
    {
        return [
            'type' => HelpType::class,
            'status' => HelpStatus::class,
            'consent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (HelpRequest $request) {
            $request->reference ??= static::nextReference();
        });
    }

    /** HR-2026-000001: short enough to read out on the phone. */
    public static function nextReference(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year) {
            $last = static::where('reference', 'like', "HR-$year-%")->lockForUpdate()->max('reference');
            $next = $last ? ((int) substr($last, -6)) + 1 : 1;

            return sprintf('HR-%d-%06d', $year, $next);
        });
    }

    public function registerMediaCollections(): void
    {
        // Prescriptions, reports, CNIC copies: kept on the private disk, never public.
        $this->addMediaCollection('document')
            ->useDisk('local')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', [HelpStatus::New, HelpStatus::Reviewing, HelpStatus::Approved]);
    }

    public function place(): ?string
    {
        return collect([$this->village, $this->district])->filter()->implode(', ') ?: null;
    }

    protected function activityArea(): string
    {
        return 'services';
    }

    protected function activityOnly(): ?array
    {
        return ['status', 'handled_by', 'staff_notes'];
    }

    protected function activityDescription(string $event): string
    {
        if ($event === 'updated' && $this->wasChanged('status')) {
            return 'Marked help request as '.strtolower($this->status->getLabel());
        }

        return ucfirst($event).' help request';
    }
}
