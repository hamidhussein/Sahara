<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
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
