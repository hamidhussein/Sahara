<?php

namespace App\Filament\Resources\Volunteers\Pages;

use App\Filament\Resources\Volunteers\VolunteerResource;
use Filament\Resources\Pages\ListRecords;

class ListVolunteers extends ListRecords
{
    protected static string $resource = VolunteerResource::class;

    public function getSubheading(): ?string
    {
        return 'Sign-ups from the Get Involved page. Call new volunteers, then mark them as contacted.';
    }
}
