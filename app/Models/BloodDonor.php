<?php

namespace App\Models;

use App\Enums\BloodGroup;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

/** The Sahara Blood Donor Society (plan 2.2). Private: only the Services team sees it. */
class BloodDonor extends Model
{
    use RecordsActivity;

    protected $guarded = ['id'];

    protected $hidden = ['ip_address'];

    protected static array $recordEvents = ['updated', 'deleted'];

    protected function casts(): array
    {
        return [
            'blood_group' => BloodGroup::class,
            'last_donated_on' => 'date',
            'is_available' => 'boolean',
            'consent_at' => 'datetime',
        ];
    }

    /** Whole blood: at least three months between donations. */
    public function canDonateAgain(): bool
    {
        return ! $this->last_donated_on || $this->last_donated_on->lte(now()->subMonths(3));
    }

    protected function activityArea(): string
    {
        return 'services';
    }

    protected function activityOnly(): ?array
    {
        return ['is_available', 'last_donated_on', 'staff_notes'];
    }

    protected function activityDescription(string $event): string
    {
        return ucfirst($event).' blood donor';
    }
}
