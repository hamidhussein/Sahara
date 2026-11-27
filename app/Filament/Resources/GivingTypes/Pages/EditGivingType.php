<?php

namespace App\Filament\Resources\GivingTypes\Pages;

use App\Filament\Resources\GivingTypes\GivingTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGivingType extends EditRecord
{
    protected static string $resource = GivingTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
