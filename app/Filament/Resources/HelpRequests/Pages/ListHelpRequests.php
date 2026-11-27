<?php

namespace App\Filament\Resources\HelpRequests\Pages;

use App\Filament\Resources\HelpRequests\HelpRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListHelpRequests extends ListRecords
{
    protected static string $resource = HelpRequestResource::class;

    public function getSubheading(): ?string
    {
        return 'Requests from the Get Help page. Showing open requests; change the Status filter to see finished ones.';
    }
}
