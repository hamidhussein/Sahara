<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')->label('View on site')->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => route('news.show', $this->record), shouldOpenInNewTab: true)
                ->visible(fn () => $this->record->published_at?->isPast()),
            DeleteAction::make(),
        ];
    }
}
