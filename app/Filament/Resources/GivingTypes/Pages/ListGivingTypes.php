<?php

namespace App\Filament\Resources\GivingTypes\Pages;

use App\Filament\Resources\GivingTypes\GivingTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGivingTypes extends ListRecords
{
    protected static string $resource = GivingTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
