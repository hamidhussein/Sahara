<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    use RecordsActivity;

    /** Visitors send messages; the log records how staff handle them, not what was written. */
    protected static array $recordEvents = ['updated', 'deleted'];

    protected function activityArea(): string
    {
        return 'messages';
    }

    protected function activityOnly(): ?array
    {
        return ['handled_at', 'handled_by', 'staff_notes'];
    }

    protected function activityDescription(string $event): string
    {
        if ($event === 'updated' && $this->wasChanged('handled_at')) {
            return $this->handled_at ? 'Marked message as handled' : 'Reopened message';
        }

        return ucfirst($event).' message';
    }

    protected $guarded = ['id'];

    protected $hidden = ['ip_address'];

    protected function casts(): array
    {
        return ['handled_at' => 'datetime'];
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function scopeOpen(Builder $query): void
    {
        $query->whereNull('handled_at');
    }

    public function contactLine(): string
    {
        return collect([$this->email, $this->phone])->filter()->implode(' · ');
    }
}
