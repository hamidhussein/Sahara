<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use App\Enums\ReportType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * A public document on the Transparency page: annual report, audited
 * accounts or registration certificate (plan 1.1, Transparency).
 */
class Report extends Model implements HasMedia
{
    use InteractsWithMedia, RecordsActivity;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => ReportType::class,
            'spending' => 'array',
            'is_published' => 'boolean',
            'year' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')
            ->singleFile()
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->orderByDesc('year')->orderBy('title');
    }

    public function fileUrl(): ?string
    {
        return $this->getFirstMediaUrl('file') ?: null;
    }

    /** File size like "1.2 MB", shown next to the download link. */
    public function fileSize(): ?string
    {
        $media = $this->getFirstMedia('file');

        return $media ? $media->human_readable_size : null;
    }

    /** Spending rows with amounts > 0, largest first, each with its share of the total. */
    public function spendingBreakdown(): array
    {
        $rows = collect($this->spending ?? [])
            ->filter(fn ($row) => filled($row['label'] ?? null) && (int) ($row['amount'] ?? 0) > 0)
            ->map(fn ($row) => ['label' => $row['label'], 'amount' => (int) $row['amount']])
            ->sortByDesc('amount')
            ->values();

        $total = $rows->sum('amount');

        return $rows->map(fn ($row) => $row + ['percent' => $total ? round($row['amount'] / $total * 100, 1) : 0])->all();
    }
}
