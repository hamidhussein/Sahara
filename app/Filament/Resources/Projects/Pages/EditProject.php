<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')->label('View on site')->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => route('projects.show', $this->record), shouldOpenInNewTab: true)
                ->visible(fn () => $this->record->is_published),
            DeleteAction::make(),
        ];
    }
}
