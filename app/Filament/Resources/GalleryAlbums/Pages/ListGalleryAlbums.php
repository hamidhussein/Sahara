<?php

namespace App\Filament\Resources\GalleryAlbums\Pages;

use App\Filament\Resources\GalleryAlbums\GalleryAlbumResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGalleryAlbums extends ListRecords
{
    protected static string $resource = GalleryAlbumResource::class;

    public function getSubheading(): ?string
    {
        return 'An album appears on the website only when it is published and contains a photo or video. Reorder albums to change their display order.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
