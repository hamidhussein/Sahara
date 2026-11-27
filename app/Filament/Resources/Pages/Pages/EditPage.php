<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')->label('View on site')->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => url('/'.$this->record->slug), shouldOpenInNewTab: true),
        ];
    }
}
