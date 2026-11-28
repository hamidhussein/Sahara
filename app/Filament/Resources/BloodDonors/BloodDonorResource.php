<?php

namespace App\Filament\Resources\BloodDonors;

use App\Enums\BloodGroup;
use App\Filament\Resources\BloodDonors\Pages\ListBloodDonors;
use App\Filament\Resources\HelpRequests\HelpRequestResource;
use App\Models\BloodDonor;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * The Sahara Blood Donor Society (plan 2.2). Done when staff can find a donor
 * by blood group and district in under a minute. Services team only.
 */
class BloodDonorResource extends Resource
{
    protected static ?string $model = BloodDonor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static string|UnitEnum|null $navigationGroup = 'Services';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(120),
            Select::make('blood_group')->options(BloodGroup::class)->required(),
            Select::make('district')->options(fn () => array_combine(config('sswf.districts'), config('sswf.districts')))->required(),
            DatePicker::make('last_donated_on')->label('Last donated')->maxDate(now()),
            Toggle::make('is_available')->label('Available to be called')
                ->helperText('Switch off if they are ill, travelling, or asked not to be called for now.'),
            Textarea::make('staff_notes')->label('Notes')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->searchPlaceholder('Search donor name or mobile')
            ->emptyStateHeading('No matching blood donors')
            ->emptyStateDescription('Choose another blood group or district, or change the Available filter to include unavailable donors.')
            ->columns([
                TextColumn::make('blood_group')->label('Group')->badge()->color('danger')->sortable(),
                TextColumn::make('name')->searchable(['name', 'phone'])->weight('semibold')->wrap()
                    ->description(fn (BloodDonor $record) => $record->phone),
                TextColumn::make('district')->sortable(),
                TextColumn::make('last_donated_on')->label('Last donated')->date('j M Y')->placeholder('Not given')->sortable()
                    ->description(fn (BloodDonor $record) => $record->canDonateAgain() ? null : 'Less than 3 months ago'),
                IconColumn::make('is_available')->label('Available')->boolean(),
            ])
            ->filters([
                SelectFilter::make('blood_group')->label('Blood group')->options(BloodGroup::class),
                SelectFilter::make('district')->options(fn () => array_combine(config('sswf.districts'), config('sswf.districts'))),
                TernaryFilter::make('is_available')->label('Available')->default(true),
            ])
            ->recordActions([
                Action::make('call')->label('Call')->icon(Heroicon::OutlinedPhone)->color('success')
                    ->url(fn (BloodDonor $record) => HelpRequestResource::callUrl($record->phone)),
                ActionGroup::make([
                    EditAction::make()->modalHeading('Blood donor details'),
                    DeleteAction::make(),
                ])->tooltip('More donor actions'),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListBloodDonors::route('/')];
    }
}
