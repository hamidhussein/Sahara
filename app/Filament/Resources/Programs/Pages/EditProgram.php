<?php

namespace App\Filament\Resources\Programs\Pages;

use App\Filament\Resources\Programs\ProgramResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProgram extends EditRecord
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')->label('View on site')->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => route('programs.show', $this->record), shouldOpenInNewTab: true)
                ->visible(fn () => $this->record->is_published),
            DeleteAction::make()
                ->disabled(fn () => $this->record->projects()->exists())
                ->tooltip(fn () => $this->record->projects()->exists() ? 'Move or delete this program’s projects first.' : null),
        ];
    }
}
