<?php

namespace App\Filament\Resources\Volunteers;

use App\Filament\Resources\HelpRequests\HelpRequestResource;
use App\Filament\Resources\Volunteers\Pages\ListVolunteers;
use App\Models\Volunteer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/** Volunteer sign-ups from the Get Involved page (plan 2.2). Services team only. */
class VolunteerResource extends Resource
{
    protected static ?string $model = Volunteer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandRaised;

    protected static string|UnitEnum|null $navigationGroup = 'Services';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        $new = Volunteer::whereNull('contacted_at')->count();

        return $new ? (string) $new : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Volunteers not contacted yet';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('area')->maxLength(120),
            CheckboxList::make('skills')->options(array_combine(Volunteer::SKILLS, Volunteer::SKILLS))->columns(2),
            TextInput::make('availability')->maxLength(160),
            Textarea::make('staff_notes')->label('Notes')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()->weight('semibold')
                    ->description(fn (Volunteer $record) => $record->phone),
                TextColumn::make('area')->searchable()->placeholder('–'),
                TextColumn::make('skills')->badge()->color('gray')->placeholder('–'),
                TextColumn::make('availability')->placeholder('–')->toggleable(),
                TextColumn::make('contacted_at')->label('Contacted')->since('Asia/Karachi')->placeholder('Not yet')->sortable(),
                TextColumn::make('created_at')->label('Signed up')->since('Asia/Karachi')->sortable(),
            ])
            ->filters([
                SelectFilter::make('skill')->label('Skill')
                    ->options(array_combine(Volunteer::SKILLS, Volunteer::SKILLS))
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'] ?? null, fn (Builder $query, $skill) => $query->whereJsonContains('skills', $skill))),
                TernaryFilter::make('contacted')->label('Contacted')
                    ->placeholder('All')->trueLabel('Contacted')->falseLabel('Not yet')
                    ->queries(true: fn (Builder $query) => $query->whereNotNull('contacted_at'), false: fn (Builder $query) => $query->whereNull('contacted_at')),
            ])
            ->recordActions([
                Action::make('call')->label('Call')->icon(Heroicon::OutlinedPhone)->color('success')
                    ->url(fn (Volunteer $record) => HelpRequestResource::callUrl($record->phone)),
                Action::make('markContacted')->label('Mark contacted')->icon(Heroicon::OutlinedCheckCircle)
                    ->visible(fn (Volunteer $record) => $record->contacted_at === null)
                    ->authorize(fn (Volunteer $record) => auth()->user()->can('update', $record))
                    ->action(function (Volunteer $record) {
                        $record->forceFill(['contacted_at' => now()])->save();
                        Notification::make()->success()->title("{$record->name} marked as contacted")->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListVolunteers::route('/')];
    }
}
