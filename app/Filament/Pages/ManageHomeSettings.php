<?php

namespace App\Filament\Pages;

use App\Enums\Role;
use App\Models\Cause;
use App\Settings\HomeSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Home page text that changes with campaigns. Editors and Super Admin. */
class ManageHomeSettings extends SettingsPage
{
    protected static string $settings = HomeSettings::class;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Home page';

    protected static ?string $title = 'Home page';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasAnyRole([Role::SuperAdmin->value, Role::Editor->value]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Top of the page')
                ->description('The first thing every visitor sees. Keep the heading short; it sits over the photo.')
                ->schema([
                    TextInput::make('hero_heading')->label('Heading')->required()->maxLength(80),
                    Textarea::make('hero_text')->label('Text under the heading')->required()->rows(3)->maxLength(300),
                    FileUpload::make('hero_image')->label('Photo')->image()->disk('public')->directory('settings')
                        ->imageEditor()->maxSize(8192)
                        // Shrunk in the browser before upload, so phones never load a huge photo.
                        ->imageResizeMode('contain')->imageResizeTargetWidth('1600')->imageResizeTargetHeight('1600')->imageResizeUpscale(false)
                        ->helperText('A wide, real SSWF photo, at least 1600 pixels across. Leave empty to keep the Danyor water photo.'),
                    TextInput::make('hero_image_alt')->label('What the photo shows')->required()->maxLength(160)
                        ->helperText('Read aloud to blind visitors, e.g. "Volunteers delivering water in Danyor".'),
                ]),

            Section::make('How your donation helps')
                ->columnSpanFull()
                ->description('Up to three examples. Leave the amount empty for "Any amount".')
                ->schema([
                    Repeater::make('tiers')->hiddenLabel()
                        ->schema([
                            TextInput::make('amount')->label('Amount')->numeric()->minValue(100)->prefix('Rs')
                                ->placeholder('Any amount'),
                            Select::make('cause')->label('Gives to')
                                ->options(fn () => Cause::active()->orderBy('name')->pluck('name', 'slug')->all())
                                ->placeholder('Where it is needed most'),
                            TextInput::make('text')->label('What it does')->required()->maxLength(140)->columnSpanFull()
                                ->placeholder('gives one family a Ramadan ration package'),
                        ])
                        ->columns(2)
                        ->minItems(1)->maxItems(3)
                        ->reorderable()
                        ->addActionLabel('Add an example'),
                ]),

            Section::make('Volunteer call')
                ->schema([
                    TextInput::make('volunteer_heading')->label('Heading')->required()->maxLength(80),
                    Textarea::make('volunteer_text')->label('Text')->required()->rows(2)->maxLength(300),
                ]),
        ]);
    }
}
