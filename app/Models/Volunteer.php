<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

/** Signed up on the Get Involved page. Private: only the Services team sees it. */
class Volunteer extends Model
{
    use RecordsActivity;

    public const SKILLS = ['Medical', 'Driving', 'Surveys', 'Media/photography', 'Teaching', 'Logistics', 'Other'];

    protected $guarded = ['id'];

    protected $hidden = ['ip_address'];

    protected static array $recordEvents = ['updated', 'deleted'];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'contacted_at' => 'datetime',
        ];
    }

    protected function activityArea(): string
    {
        return 'services';
    }

    protected function activityOnly(): ?array
    {
        return ['contacted_at', 'staff_notes'];
    }

    protected function activityDescription(string $event): string
    {
        if ($event === 'updated' && $this->wasChanged('contacted_at') && $this->contacted_at) {
            return 'Contacted volunteer';
        }

        return ucfirst($event).' volunteer';
    }
}
