<?php

namespace App\Filament\Resources\GalleryAlbums;

use App\Filament\Resources\GalleryAlbums\Pages\CreateGalleryAlbum;
use App\Filament\Resources\GalleryAlbums\Pages\EditGalleryAlbum;
use App\Filament\Resources\GalleryAlbums\Pages\ListGalleryAlbums;
use App\Models\GalleryAlbum;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Gallery albums: photos and YouTube videos (plan 1.1, Gallery). */
class GalleryAlbumResource extends Resource
{
    protected static ?string $model = GalleryAlbum::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Gallery';

    protected static ?string $modelLabel = 'album';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Album details')->columns(2)->schema([
                TextInput::make('title')->required()->maxLength(160),
                DatePicker::make('taken_on')->label('When')->helperText('Optional; shown as month and year.'),
                Textarea::make('description')->rows(2)->maxLength(400)->columnSpanFull(),
                Toggle::make('is_published')->label('Shown in the Gallery')->default(true)
                    ->helperText('Albums only appear once they have at least one photo or video.'),
            ]),
            Section::make('Photos')
                ->description('Upload photos and drag them into order. Use real SSWF photos with consent from the people shown. Large photos are resized automatically.')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('photos')->collection('photos')->hiddenLabel()
                        ->image()->multiple()->reorderable()->panelLayout('grid')
                        ->maxFiles(80)->maxSize(8 * 1024),
                ]),
            Section::make('Videos')
                ->description('YouTube links. Videos play only when a visitor taps them.')
                ->collapsed(fn (?GalleryAlbum $record) => empty($record?->videos))
                ->schema([
                    Repeater::make('videos')->hiddenLabel()
                        ->schema([
                            TextInput::make('url')->label('YouTube link')->required()->url()
                                ->rule(fn () => function (string $attribute, $value, $fail) {
                                    if (! GalleryAlbum::youtubeId((string) $value)) {
                                        $fail('Paste a YouTube link, e.g. https://youtu.be/abc123XYZ00.');
                                    }
                                }),
                            TextInput::make('title')->label('Title (optional)')->maxLength(120),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Add a video'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->searchPlaceholder('Search album titles')
            ->emptyStateHeading('No albums found')
            ->emptyStateDescription('Create an album and add photos or videos, or clear your search.')
            ->reorderable('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('photos')->collection('photos')->conversion('thumb')->limit(1)->label(''),
                TextColumn::make('title')->weight('semibold')->searchable()->wrap(),
                TextColumn::make('media_count')->counts('media')->label('Photos'),
                TextColumn::make('public_visibility')->label('Public visibility')->badge()
                    ->state(fn (GalleryAlbum $record): string => match (true) {
                        ! $record->is_published => 'Hidden',
                        ! $record->media_count && empty($record->videos) => 'Needs photos or videos',
                        default => 'Published',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Published' => 'success',
                        'Hidden' => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('taken_on')->label('When')->date('M Y')->placeholder('–'),
                ToggleColumn::make('is_published')->label('Published')->toggleable(),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGalleryAlbums::route('/'),
            'create' => CreateGalleryAlbum::route('/create'),
            'edit' => EditGalleryAlbum::route('/{record}/edit'),
        ];
    }
}
