<?php

namespace App\Filament\Resources\BloodDonors\Pages;

use App\Filament\Resources\BloodDonors\BloodDonorResource;
use Filament\Resources\Pages\ListRecords;

class ListBloodDonors extends ListRecords
{
    protected static string $resource = BloodDonorResource::class;

    public function getSubheading(): ?string
    {
        return 'Find a donor: choose the blood group and district in the filters. Showing donors available to be called.';
    }
}
