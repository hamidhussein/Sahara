<?php

namespace App\Filament\Resources\Partners\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->columns(2)->schema([
                    TextInput::make('name')->required()->maxLength(200),
                    TextInput::make('url')->label('Website')->url(),
                    SpatieMediaLibraryFileUpload::make('logo')->collection('logo')->image()->maxSize(2048)
                        ->helperText('Optional. Without a logo, the name is shown.'),
                    Toggle::make('is_published')->label('Show on the home page')->default(true),
                ]),
            ]);
    }
}
