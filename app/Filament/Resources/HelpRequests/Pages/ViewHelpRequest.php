<?php

namespace App\Filament\Resources\HelpRequests\Pages;

use App\Filament\Resources\HelpRequests\HelpRequestResource;
use App\Models\HelpRequest;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewHelpRequest extends ViewRecord
{
    protected static string $resource = HelpRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('call')->label('Call')->icon(Heroicon::OutlinedPhone)->color('success')
                ->url(fn (HelpRequest $record) => HelpRequestResource::callUrl($record->phone)),
            HelpRequestResource::whatsappAction(),
            HelpRequestResource::updateStatusAction(),
            DeleteAction::make()->modalDescription('Only delete spam or duplicates. Finished requests should be marked "Done" instead, so there is a record.'),
        ];
    }
}
