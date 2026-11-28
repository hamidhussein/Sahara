<?php

namespace App\Filament\Pages;

use App\Enums\Role;
use App\Models\Cause;
use App\Models\GalleryAlbum;
use App\Settings\HomeSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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

    /** No slides saved yet: start the list with the single hero that was set before. */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (blank($data['hero_slides'] ?? null)) {
            $data['hero_slides'] = [[
                'heading' => $data['hero_heading'] ?? '',
                'text' => $data['hero_text'] ?? '',
                'image' => $data['hero_image'] ?? null,
                'image_alt' => $data['hero_image_alt'] ?? '',
                'preset' => 'danyor-water',
            ]];
        }

        return $data;
    }

    /** Slide 1 is also the page's share preview and search description photo. */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['hero_slides'] = array_values($data['hero_slides'] ?? []);

        if ($first = $data['hero_slides'][0] ?? null) {
            $data['hero_heading'] = $first['heading'];
            $data['hero_text'] = $first['text'];
            $data['hero_image'] = $first['image'] ?? null;
            $data['hero_image_alt'] = $first['image_alt'];
        }

        return $data;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Hero slides')
                ->description('The top of the page. Each slide has its own photo and message; the Donate Now and Get Help buttons stay on every slide. Keep headings short: they sit over the photo.')
                ->schema([
                    Repeater::make('hero_slides')->hiddenLabel()
                        ->schema([
                            TextInput::make('heading')->label('Heading')->required()->maxLength(80),
                            Textarea::make('text')->label('Text under the heading')->required()->rows(2)->maxLength(300),
                            FileUpload::make('image')->label('Photo')->image()->disk('public')->directory('settings/hero')
                                ->imageEditor()->maxSize(8192)
                                // Shrunk in the browser before upload, so phones never load a huge photo.
                                ->automaticallyResizeImagesMode('contain')->automaticallyResizeImagesToWidth('1600')->automaticallyResizeImagesToHeight('1600')->automaticallyUpscaleImagesWhenResizing(false)
                                ->helperText('A wide, real SSWF photo, at least 1600 pixels across.'),
                            Select::make('preset')->label('Built-in photo, used while no photo is uploaded')
                                ->options(collect(HomeSettings::HERO_PRESETS)->map(fn ($preset) => $preset['label'])->all())
                                ->default('danyor-water')->selectablePlaceholder(false),
                            TextInput::make('image_alt')->label('What the photo shows')->required()->maxLength(160)->columnSpanFull()
                                ->helperText('Read aloud to blind visitors, e.g. "Volunteers delivering water in Danyor".'),
                        ])
                        ->columns(2)
                        ->minItems(1)->maxItems(5)
                        ->reorderable()->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['heading'] ?? null)
                        ->addActionLabel('Add a slide'),
                    Toggle::make('hero_autoplay')->label('Move to the next slide automatically')
                        ->helperText('Moves on every few seconds (set below). When a visitor picks a slide, the countdown starts again. It never moves for people whose device asks for reduced motion. Switch it off if you prefer the first slide to stay.')
                        ->live(),
                    TextInput::make('hero_interval')->label('Seconds per slide')->numeric()->integer()->required()
                        ->minValue(4)->maxValue(15)->suffix('seconds')
                        ->visible(fn ($get) => (bool) $get('hero_autoplay')),
                ]),

            Section::make('About section video')
                ->description('Optional YouTube video played in a modal when a visitor clicks the About photo collage.')
                ->columns(2)
                ->schema([
                    TextInput::make('about_video_url')->label('YouTube link')->url()->nullable()
                        ->placeholder('https://www.youtube.com/watch?v=...')
                        ->rule(fn () => function (string $attribute, $value, $fail): void {
                            if (filled($value) && ! GalleryAlbum::youtubeId((string) $value)) {
                                $fail('Paste a YouTube link, e.g. https://youtu.be/abc123XYZ00.');
                            }
                        }),
                    TextInput::make('about_video_title')->label('Video title (optional)')->maxLength(120)
                        ->placeholder('Subka Sahara Welfare Foundation story'),
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
