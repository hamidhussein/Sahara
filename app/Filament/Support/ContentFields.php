<?php

namespace App\Filament\Support;

use App\Enums\ContentLanguage;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

/**
 * Fields every content form shares (plan 1.3: "Every content form includes
 * the Language field, SEO fields and a preview button").
 */
class ContentFields
{
    /** Title input that fills the slug while the slug is still untouched. */
    public static function title(string $name = 'title', string $label = 'Title'): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->required()
            ->maxLength(200)
            ->live(onBlur: true)
            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                if (blank($get('slug')) || $get('slug') === Str::slug((string) $old)) {
                    $set('slug', Str::slug((string) $state));
                }
            });
    }

    public static function slug(string $routePrefix): TextInput
    {
        return TextInput::make('slug')
            ->label('Web address')
            ->prefix(url($routePrefix).'/')
            ->helperText('Filled from the title. Use short English words, e.g. miachar-water-plant. Urdu titles are spelled out in Latin letters, so you may want to replace them with English words.')
            ->maxLength(200)
            ->alphaDash()
            ->unique(ignoreRecord: true);
    }

    public static function language(): Select
    {
        return Select::make('language')
            ->label('Language')
            ->options(ContentLanguage::class)
            ->default(ContentLanguage::English)
            ->required()
            ->native(false)
            ->helperText('Choose اردو to show this content right-to-left in Urdu fonts. The menu and footer stay in English.');
    }

    public static function cover(string $label = 'Cover photo'): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make('cover')
            ->collection('cover')
            ->label($label)
            ->image()
            ->imageEditor()
            ->maxSize(8 * 1024)
            ->helperText('Real SSWF photos only. Ask consent before showing beneficiaries. Large photos are resized to WebP automatically.');
    }

    public static function gallery(): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make('gallery')
            ->collection('gallery')
            ->label('Gallery')
            ->image()
            ->multiple()
            ->reorderable()
            ->maxFiles(30)
            ->maxSize(8 * 1024);
    }

    public static function seo(): Section
    {
        return Section::make('Search and sharing')
            ->description('How this appears on Google and when shared on WhatsApp or Facebook. Leave empty to use the title and summary.')
            ->collapsed()
            ->schema([
                TextInput::make('seo_title')->label('Search title')->maxLength(70),
                Textarea::make('seo_description')->label('Search description')->rows(2)->maxLength(160),
            ]);
    }
}
