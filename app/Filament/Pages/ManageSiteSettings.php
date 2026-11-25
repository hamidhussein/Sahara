<?php

namespace App\Filament\Pages;

use App\Settings\SiteSettings;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSiteSettings extends SettingsPage
{
    protected static string $settings = SiteSettings::class;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?string $navigationLabel = 'Contact & social';

    protected static ?string $title = 'Contact details and social links';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole('Super Admin');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Phone numbers')
                ->description('Shown in the emergency strip, the sticky mobile bar and the footer on every page. While the ambulance number is empty, the site shows the office phone instead. While WhatsApp is empty, WhatsApp buttons are hidden.')
                ->columns(3)
                ->schema([
                    TextInput::make('office_phone')->label('Office phone')->tel()->required(),
                    TextInput::make('ambulance_phone')->label('Ambulance & emergency')->tel(),
                    TextInput::make('whatsapp_number')->label('WhatsApp')->tel()
                        ->helperText('International format, e.g. +92 3xx xxxxxxx'),
                ]),
            Section::make('Office')
                ->columns(2)
                ->schema([
                    TextInput::make('email')->email()->required(),
                    TextInput::make('office_hours')->placeholder('Monday–Saturday, 9:00 am – 5:00 pm'),
                    TextInput::make('address')->required()->columnSpanFull(),
                    TextInput::make('registration_number')->label('Registration number')->required()
                        ->helperText('Printed in the footer, on receipts and on the Transparency page.'),
                ]),
            Section::make('Chairman’s message')
                ->description('Shown at the top of the Leadership page. It stays hidden while empty. Paste the text only after the chairman has approved it.')
                ->collapsed()
                ->schema([
                    Textarea::make('chairman_message')->hiddenLabel()->rows(5)->maxLength(1200)
                        ->placeholder('Draft from the plan, for the chairman to review: “When we started, we had little more than a few volunteers and a strong wish to help. Today, thanks to Allah and to the trust of our donors and community, Sahara reaches families across Gilgit-Baltistan. Our promise remains the same: every rupee you give will reach the people who need it, and we will show you where it went.”'),
                ]),
            Section::make('Social links')
                ->description('Leave empty to hide a link.')
                ->columns(2)
                ->schema([
                    TextInput::make('facebook_url')->label('Facebook')->url(),
                    TextInput::make('instagram_url')->label('Instagram')->url(),
                    TextInput::make('youtube_url')->label('YouTube')->url(),
                    TextInput::make('tiktok_url')->label('TikTok')->url(),
                ]),
        ]);
    }
}
