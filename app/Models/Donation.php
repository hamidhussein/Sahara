<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use App\Enums\DonationStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Donation extends Model implements HasMedia
{
    use InteractsWithMedia, RecordsActivity;

    /** Donors create donations on the website; the log records what staff and gateways do after that. */
    protected static array $recordEvents = ['updated', 'deleted'];

    protected function activityArea(): string
    {
        return 'donations';
    }

    /** Status and Finance's notes only: never the donor's name, phone or email. */
    protected function activityOnly(): ?array
    {
        return ['status', 'amount', 'finance_notes', 'needs_eligibility_review', 'confirmed_by', 'paid_at'];
    }

    protected function activityDescription(string $event): string
    {
        if ($event === 'updated' && $this->wasChanged('status')) {
            return match ($this->status) {
                DonationStatus::Paid => 'Confirmed payment',
                DonationStatus::Refunded => 'Refunded donation',
                DonationStatus::Failed => 'Marked donation as failed',
                default => 'Changed donation status',
            };
        }

        return ucfirst($event).' donation';
    }

    protected $guarded = ['id'];

    protected $hidden = ['gateway_payload', 'ip_address'];

    protected function casts(): array
    {
        return [
            'status' => DonationStatus::class,
            'payment_method' => PaymentMethod::class,
            'amount' => 'integer',
            'is_anonymous' => 'boolean',
            'needs_eligibility_review' => 'boolean',
            'gateway_payload' => 'array',
            'paid_at' => 'datetime',
            'receipt_sent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Donation $donation) {
            $donation->reference ??= static::nextReference();
        });
    }

    /**
     * Bank transfer receipts are private: stored on the local disk, never in public
     * storage, and viewable only by Finance and Super Admin.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('transfer_receipt')
            ->useDisk('local')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);
    }

    public function givingType(): BelongsTo
    {
        return $this->belongsTo(GivingType::class);
    }

    public function cause(): BelongsTo
    {
        return $this->belongsTo(Cause::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function scopePaid(Builder $query): void
    {
        $query->where('status', DonationStatus::Paid);
    }

    /** SSWF-2026-000123: readable on the phone and unique per year. */
    public static function nextReference(): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($year) {
            $last = static::where('reference', 'like', "SSWF-$year-%")->lockForUpdate()->max('reference');
            $next = $last ? ((int) substr($last, -6)) + 1 : 1;

            return sprintf('SSWF-%d-%06d', $year, $next);
        });
    }

    public function causeLabel(): string
    {
        return $this->cause?->name ?? 'Where it is needed most';
    }

    public function formattedAmount(): string
    {
        return 'Rs '.number_format($this->amount);
    }

    public function markPaid(?User $confirmedBy = null, ?string $transactionId = null, array $payload = []): void
    {
        $this->forceFill([
            'status' => DonationStatus::Paid,
            'paid_at' => $this->paid_at ?? now(),
            'confirmed_by' => $confirmedBy?->getKey() ?? $this->confirmed_by,
            'gateway_transaction_id' => $transactionId ?? $this->gateway_transaction_id,
            'gateway_payload' => $payload ?: $this->gateway_payload,
        ])->save();
    }
}
