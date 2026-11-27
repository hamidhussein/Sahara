<?php

namespace App\Filament\Resources\ActivityLog\Pages;

use App\Filament\Resources\ActivityLog\ActivityLogResource;
use Filament\Resources\Pages\ListRecords;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    public function getSubheading(): ?string
    {
        return 'Who changed what, and when. Entries are kept for a year. Donors’ personal details and passwords are never recorded.';
    }
}
