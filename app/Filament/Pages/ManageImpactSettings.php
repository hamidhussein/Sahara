<?php

namespace App\Filament\Pages;

use App\Settings\ImpactSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageImpactSettings extends SettingsPage
{
    protected static string $settings = ImpactSettings::class;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Impact numbers';

    protected static ?string $title = 'Home page impact numbers';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole('Super Admin');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Counters on the home page')
                ->description('Update these each quarter. Years of service are calculated from the founding year.')
                ->columns(3)
                ->schema([
                    TextInput::make('founded_year')->label('Founded in')->numeric()->required()->minValue(1990)->maxValue(2100),
                    TextInput::make('water_plants')->label('Water filtration plants')->numeric()->required()->minValue(0),
                    TextInput::make('report_year')->label('Year the figures below refer to')->numeric()->required()->minValue(2006)->maxValue(2100),
                    TextInput::make('patients_examined')->label('Patients examined at eye camp')->numeric()->required()->minValue(0),
                    TextInput::make('eye_operations')->label('Free eye operations')->numeric()->required()->minValue(0),
                    TextInput::make('ramadan_families')->label('Families given Ramadan rations')->numeric()->required()->minValue(0),
                ]),
        ]);
    }
}
