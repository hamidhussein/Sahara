<?php

namespace App\Filament\Resources\GivingTypes;

use App\Filament\Resources\GivingTypes\Pages\CreateGivingType;
use App\Filament\Resources\GivingTypes\Pages\EditGivingType;
use App\Filament\Resources\GivingTypes\Pages\ListGivingTypes;
use App\Models\GivingType;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Each type of giving is its own fund (plan 1.2). */
class GivingTypeResource extends Resource
{
    protected static ?string $model = GivingType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static string|UnitEnum|null $navigationGroup = 'Donations';

    protected static ?string $navigationLabel = 'Types of giving';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(120),
                TextInput::make('slug')->label('Short code')->alphaDash()->unique(ignoreRecord: true)
                    ->helperText('Used in links, e.g. /donate?type=zakat. Filled from the name.'),
                Textarea::make('description')->rows(2)->maxLength(300)->columnSpanFull()
                    ->helperText('Shown under the name on the Donate page.'),
                Toggle::make('is_restricted')->label('Restricted fund')
                    ->helperText('Restricted funds (Zakat, Khums) only go to causes marked eligible for them. Gifts to other causes are flagged for review.'),
                Toggle::make('is_active')->label('Shown on the Donate page')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')->weight('semibold')->searchable(),
                IconColumn::make('is_restricted')->label('Restricted')->boolean(),
                TextColumn::make('donations_count')->counts('donations')->label('Donations'),
                ToggleColumn::make('is_active')->label('Shown'),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGivingTypes::route('/'),
            'create' => CreateGivingType::route('/create'),
            'edit' => EditGivingType::route('/{record}/edit'),
        ];
    }
}
