<?php

namespace App\Filament\Pages;

use App\Settings\DonationSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageDonationSettings extends SettingsPage
{
    protected static string $settings = DonationSettings::class;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $navigationLabel = 'Bank & Fitrana';

    protected static ?string $title = 'Bank details and Fitrana';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole('Super Admin');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Bank transfer details')
                ->description('Shown on the Donate page for bank transfer and Raast, and to overseas donors.')
                ->columns(2)
                ->schema([
                    TextInput::make('bank_name')->label('Bank and branch')->required(),
                    TextInput::make('account_title')->label('Account title')
                        ->helperText('Confirm after the name change.'),
                    TextInput::make('account_number')->label('Account number')->required(),
                    TextInput::make('iban')->label('IBAN'),
                    TextInput::make('swift_code')->label('SWIFT code'),
                ]),
            Section::make('Fitrana and Zakat')
                ->columns(2)
                ->schema([
                    TextInput::make('fitrana_amount')->label('Fitrana per person this year')
                        ->numeric()->minValue(0)->prefix('Rs')
                        ->helperText('Set before Eid-ul-Fitr each year. Leave empty until it is announced.'),
                    TextInput::make('nisab_amount')->label('Nisab for the Zakat calculator')
                        ->numeric()->minValue(0)->prefix('Rs')
                        ->helperText('Update when silver and gold prices change. While empty, the calculator asks donors to check the nisab with us or a scholar.'),
                ]),
        ]);
    }
}
