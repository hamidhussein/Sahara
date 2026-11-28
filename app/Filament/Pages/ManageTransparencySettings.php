<?php

namespace App\Filament\Pages;

use App\Enums\Role;
use App\Settings\TransparencySettings;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Funding, assets and the Zakat and Khums policy on the Transparency page. Finance and Super Admin. */
class ManageTransparencySettings extends SettingsPage
{
    protected static string $settings = TransparencySettings::class;

    protected static string|UnitEnum|null $navigationGroup = 'Transparency';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?string $navigationLabel = 'Policy and funding';

    protected static ?string $title = 'Transparency page: policy and funding';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasAnyRole([Role::SuperAdmin->value, Role::Finance->value]);
    }

    private const LISTS = ['funding', 'assets', 'policy'];

    /** One line per item; drag to reorder. Saved as a plain list of strings. */
    protected static function lines(string $name, string $label, string $placeholder, int $min = 1): Repeater
    {
        return Repeater::make($name)->label($label)
            ->schema([TextInput::make('line')->hiddenLabel()->required()->maxLength(300)->placeholder($placeholder)])
            ->minItems($min)->reorderable()->addActionLabel('Add a line');
    }

    /** Plain strings → one repeater item each. */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach (self::LISTS as $list) {
            $data[$list] = array_map(fn ($line) => ['line' => $line], array_values($data[$list] ?? []));
        }

        return $data;
    }

    /** Repeater items → plain strings, in the order shown. */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        foreach (self::LISTS as $list) {
            $data[$list] = array_values(array_filter(array_map(fn ($item) => trim((string) ($item['line'] ?? '')), $data[$list] ?? [])));
        }

        return $data;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('How we are funded')->schema([
                static::lines('funding', 'Sources of income', 'Donation boxes'),
            ]),
            Section::make('What we own')->schema([
                static::lines('assets', 'Assets', '3 ambulances'),
            ]),
            Section::make('Our Zakat and Khums policy')
                ->description('Donors read these rules before giving Zakat or Khums. Check changes with the chairman.')
                ->schema([
                    static::lines('policy', 'Rules', 'Zakat and Fitrana go only to eligible (mustahiq) individuals and families.'),
                ]),
        ]);
    }
}
