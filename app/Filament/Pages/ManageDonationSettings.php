<?php

namespace App\Filament\Pages;

use App\Payments\SampleDetails;
use App\Settings\DonationSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Where donors send money (bank, Raast, JazzCash, Easypaisa) with the QR codes
 * the providers issue, plus Fitrana and the Zakat nisab. Super Admin only.
 */
class ManageDonationSettings extends SettingsPage
{
    protected static string $settings = DonationSettings::class;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $navigationLabel = 'Bank, wallets & Fitrana';

    protected static ?string $title = 'Bank, wallets and Fitrana';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole('Super Admin');
    }

    /** QR code upload: the image issued by the bank or wallet provider, never one made up. */
    protected static function qrUpload(string $field, string $provider): FileUpload
    {
        return FileUpload::make($field)
            ->label("{$provider} QR code")
            ->image()
            ->disk('public')
            ->directory('payment-qr')
            ->maxSize(2048)
            ->helperText("Upload the QR code image issued by {$provider} for this account (merchant or Raast QR). Without one, donors see the number only.");
    }

    public function form(Schema $schema): Schema
    {
        $settings = app(DonationSettings::class);
        $hasSamples = collect([
            $settings->jazzcash_number, $settings->easypaisa_number, $settings->raast_id,
            $settings->jazzcash_qr, $settings->easypaisa_qr, $settings->bank_qr,
        ])->contains(fn ($v) => SampleDetails::isSample($v));

        return $schema->components([
            Text::make('Some details below are samples for testing. The live site ignores them: replace every SAMPLE value and QR code with SSWF’s real details before launch.')
                ->color('danger')
                ->weight('bold')
                ->visible($hasSamples),

            Section::make('Bank transfer and Raast')
                ->description('Shown on the Donate page under Bank Transfer / Raast, and to overseas donors.')
                ->columns(2)
                ->schema([
                    TextInput::make('bank_name')->label('Bank and branch')->required(),
                    TextInput::make('account_title')->label('Account title')
                        ->helperText('Confirm after the name change.'),
                    TextInput::make('account_number')->label('Account number')->required(),
                    TextInput::make('iban')->label('IBAN'),
                    TextInput::make('swift_code')->label('SWIFT code'),
                    TextInput::make('raast_id')->label('Raast ID')
                        ->helperText('The mobile number registered for Raast on this account, if any.'),
                    static::qrUpload('bank_qr', 'the bank (Raast QR)')->columnSpanFull(),
                ]),

            Section::make('JazzCash')
                ->description('Donors send money to this JazzCash account and upload the confirmation. Leave the number empty to hide JazzCash.')
                ->columns(2)
                ->schema([
                    TextInput::make('jazzcash_title')->label('Account title'),
                    TextInput::make('jazzcash_number')->label('JazzCash mobile account number')->tel()
                        ->placeholder('03xx xxxxxxx'),
                    static::qrUpload('jazzcash_qr', 'JazzCash')->columnSpanFull(),
                ]),

            Section::make('Easypaisa')
                ->description('Donors send money to this Easypaisa account and upload the confirmation. Leave the number empty to hide Easypaisa.')
                ->columns(2)
                ->schema([
                    TextInput::make('easypaisa_title')->label('Account title'),
                    TextInput::make('easypaisa_number')->label('Easypaisa mobile account number')->tel()
                        ->placeholder('03xx xxxxxxx'),
                    static::qrUpload('easypaisa_qr', 'Easypaisa')->columnSpanFull(),
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
