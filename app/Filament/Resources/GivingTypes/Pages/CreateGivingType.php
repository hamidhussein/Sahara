<?php

namespace App\Filament\Resources\GivingTypes\Pages;

use App\Filament\Resources\GivingTypes\GivingTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGivingType extends CreateRecord
{
    protected static string $resource = GivingTypeResource::class;
}
