<?php

namespace App\Filament\Resources\Causes;

use App\Filament\Resources\Causes\Pages\CreateCause;
use App\Filament\Resources\Causes\Pages\EditCause;
use App\Filament\Resources\Causes\Pages\ListCauses;
use App\Filament\Support\ContentFields;
use App\Models\Cause;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/** What donations are for; appeals with goals appear on the home page (plan 1.2). */
class CauseResource extends Resource
{
    protected static ?string $model = Cause::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static string|UnitEnum|null $navigationGroup = 'Donations';

    protected static ?string $navigationLabel = 'Causes & appeals';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Section::make()->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(120),
                TextInput::make('slug')->label('Short code')->alphaDash()->unique(ignoreRecord: true)
                    ->helperText('Used in links, e.g. /donate?cause=winter-package. Filled from the name.'),
                Select::make('program_id')->label('Program')->relationship('program', 'name')->native(false)
                    ->helperText('Program pages’ “Donate” buttons pick this cause.'),
                Toggle::make('is_active')->label('Shown on the Donate page')->default(true)->inline(false),
            ]),
            Section::make('Zakat and Khums')
                ->description('Tick a fund only where it is religiously allowed for this cause. Gifts from unticked restricted funds are flagged so Finance contacts the donor first.')
                ->schema([
                    CheckboxList::make('eligibleGivingTypes')->hiddenLabel()
                        ->relationship('eligibleGivingTypes', 'name', fn (Builder $query) => $query->where('is_restricted', true)),
                ]),
            Section::make('Appeal on the home page')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Toggle::make('is_appeal')->label('Show as an appeal on the home page')->live()->columnSpanFull(),
                    TextInput::make('appeal_title')->placeholder('Winter Package 2026')->visible(fn (Get $get) => $get('is_appeal')),
                    TextInput::make('goal_amount')->label('Goal')->numeric()->minValue(1)->prefix('Rs')
                        ->helperText('Leave empty to show the appeal without a progress bar.')
                        ->visible(fn (Get $get) => $get('is_appeal')),
                    Textarea::make('description')->label('Appeal text')->rows(3)->maxLength(400)->columnSpanFull()
                        ->visible(fn (Get $get) => $get('is_appeal')),
                    ContentFields::cover('Appeal photo')->columnSpanFull()->visible(fn (Get $get) => $get('is_appeal')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withPaidTotal())
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')->weight('semibold')->searchable()
                    ->description(fn (Cause $record) => $record->is_appeal ? 'Appeal: '.($record->appeal_title ?: $record->name) : null),
                TextColumn::make('paid_total')->label('Raised')->money('PKR', locale: 'en_PK', decimalPlaces: 0)->placeholder('Rs 0'),
                TextColumn::make('goal_amount')->label('Goal')->money('PKR', locale: 'en_PK', decimalPlaces: 0)->placeholder('–'),
                TextColumn::make('eligibleGivingTypes.name')->label('Accepts')->badge()->placeholder('No restricted funds'),
                IconColumn::make('is_appeal')->label('Appeal')->boolean(),
                ToggleColumn::make('is_active')->label('Shown'),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCauses::route('/'),
            'create' => CreateCause::route('/create'),
            'edit' => EditCause::route('/{record}/edit'),
        ];
    }
}
