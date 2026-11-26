<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/** Messages from the Contact page form. */
class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Messages';

    protected static ?string $modelLabel = 'message';

    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'subject';

    public static function getNavigationBadge(): ?string
    {
        $open = ContactMessage::open()->count();

        return $open ? (string) $open : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Messages not yet answered';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('Message')->columnSpan(2)->schema([
                TextEntry::make('subject')->weight('bold')->size('lg'),
                TextEntry::make('message')->hiddenLabel()->prose(),
            ]),
            Section::make('From')->schema([
                TextEntry::make('name'),
                TextEntry::make('email')->placeholder('–')->copyable()
                    ->url(fn (ContactMessage $r) => $r->email ? 'mailto:'.$r->email.'?subject='.rawurlencode('Re: '.$r->subject) : null),
                TextEntry::make('phone')->placeholder('–')->copyable()
                    ->url(fn (ContactMessage $r) => $r->phone ? 'tel:'.preg_replace('/[^0-9+]/', '', $r->phone) : null),
                TextEntry::make('created_at')->label('Received')->dateTime('j M Y, g:i a'),
                TextEntry::make('handled_at')->label('Answered')->dateTime('j M Y, g:i a')->placeholder('Not yet'),
                TextEntry::make('handledBy.name')->label('Answered by')->placeholder('–'),
            ]),
            Section::make('Staff notes')->columnSpanFull()->schema([
                TextEntry::make('staff_notes')->hiddenLabel()->placeholder('No notes.')->prose(),
            ]),
        ]);
    }

    public static function markAnswered(): Action
    {
        return Action::make('markAnswered')
            ->label('Mark answered')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->schema([
                Textarea::make('staff_notes')->label('Notes (optional)')->rows(3)
                    ->placeholder('Called back on 26 Nov, referred to the ambulance team.'),
            ])
            ->fillForm(fn (ContactMessage $record) => ['staff_notes' => $record->staff_notes])
            ->visible(fn (ContactMessage $record) => $record->handled_at === null)
            ->authorize(fn (ContactMessage $record) => auth()->user()->can('update', $record))
            ->action(fn (ContactMessage $record, array $data) => $record->forceFill([
                'handled_at' => now(),
                'handled_by' => auth()->id(),
                'staff_notes' => $data['staff_notes'],
            ])->save());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('handled_at')->label('')->boolean()
                    ->state(fn (ContactMessage $r) => $r->handled_at !== null)
                    ->trueIcon('heroicon-o-check-circle')->falseIcon('heroicon-o-envelope')
                    ->trueColor('gray')->falseColor('warning')
                    ->tooltip(fn (ContactMessage $r) => $r->handled_at ? 'Answered' : 'Not yet answered'),
                TextColumn::make('subject')->weight('semibold')->searchable()->wrap()
                    ->description(fn (ContactMessage $r) => str($r->message)->limit(90)),
                TextColumn::make('name')->searchable()->description(fn (ContactMessage $r) => $r->contactLine()),
                TextColumn::make('created_at')->label('Received')->since()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('answered')->label('Status')
                    ->placeholder('All')->trueLabel('Answered')->falseLabel('Not yet answered')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('handled_at'),
                        false: fn (Builder $query) => $query->whereNull('handled_at'),
                    )
                    ->default(false),
            ])
            ->recordActions([ViewAction::make(), static::markAnswered()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
            'view' => ViewContactMessage::route('/{record}'),
        ];
    }
}
