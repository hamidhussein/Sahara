<?php

namespace App\Filament\Resources\HelpRequests;

use App\Enums\HelpStatus;
use App\Enums\HelpType;
use App\Filament\Resources\HelpRequests\Pages\ListHelpRequests;
use App\Filament\Resources\HelpRequests\Pages\ViewHelpRequest;
use App\Models\HelpRequest;
use App\Support\PhoneNumber;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/** Requests from the Get Help page (plan 2.2). Services team only. */
class HelpRequestResource extends Resource
{
    protected static ?string $model = HelpRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;

    protected static string|UnitEnum|null $navigationGroup = 'Services';

    protected static ?string $navigationLabel = 'Help requests';

    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'reference';

    public static function getNavigationBadge(): ?string
    {
        $new = HelpRequest::where('status', HelpStatus::New)->count();

        return $new ? (string) $new : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'New requests nobody has picked up yet';
    }

    public static function callUrl(string $phone): string
    {
        return 'tel:'.preg_replace('/[^0-9+]/', '', $phone);
    }

    public static function whatsappUrl(string $phone): ?string
    {
        $digits = PhoneNumber::international($phone);

        return $digits ? 'https://wa.me/'.$digits : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make(fn (HelpRequest $record) => $record->type->getLabel())->columnSpan(2)->schema([
                TextEntry::make('details')->hiddenLabel()->prose(),
                TextEntry::make('person_in_need')->label('Person in need')->placeholder('The person asking'),
                TextEntry::make('document')->label('Document')
                    ->state(fn (HelpRequest $record) => $record->hasMedia('document') ? 'Open the document' : null)
                    ->url(fn (HelpRequest $record) => $record->hasMedia('document') ? route('services.help-document', $record) : null, shouldOpenInNewTab: true)
                    ->color('primary')
                    ->placeholder('None attached'),
            ]),
            Section::make('Contact')->schema([
                TextEntry::make('reference')->weight('bold')->copyable(),
                TextEntry::make('status')->badge(),
                TextEntry::make('name'),
                TextEntry::make('phone')->label('Mobile')->copyable()
                    ->url(fn (HelpRequest $record) => static::callUrl($record->phone)),
                TextEntry::make('place')->state(fn (HelpRequest $record) => $record->place())->placeholder('–'),
                TextEntry::make('created_at')->label('Received')->dateTime('j M Y, g:i a', 'Asia/Karachi'),
                TextEntry::make('handledBy.name')->label('Handled by')->placeholder('Nobody yet'),
            ]),
            Section::make('Staff notes')->columnSpanFull()->schema([
                TextEntry::make('staff_notes')->hiddenLabel()->placeholder('No notes yet.')->prose(),
            ]),
        ]);
    }

    /** Move the request along: new → reviewing → approved → done, with a note. */
    public static function updateStatusAction(): Action
    {
        return Action::make('updateStatus')
            ->label('Update status')
            ->icon(Heroicon::OutlinedArrowPath)
            ->schema([
                Select::make('status')->options(HelpStatus::class)->required(),
                Textarea::make('staff_notes')->label('Notes')->rows(4)
                    ->placeholder('Called on 27 Nov; two donors arranged at DHQ Gilgit.'),
            ])
            ->fillForm(fn (HelpRequest $record) => ['status' => $record->status, 'staff_notes' => $record->staff_notes])
            ->authorize(fn (HelpRequest $record) => auth()->user()->can('update', $record))
            ->action(function (HelpRequest $record, array $data) {
                $record->forceFill([
                    'status' => $data['status'],
                    'staff_notes' => $data['staff_notes'],
                    'handled_by' => auth()->id(),
                ])->save();
                Notification::make()->success()->title('Request updated')->send();
            });
    }

    public static function whatsappAction(): Action
    {
        return Action::make('whatsapp')
            ->label('WhatsApp')
            ->icon(Heroicon::OutlinedChatBubbleLeftRight)
            ->color('gray')
            ->url(fn (HelpRequest $record) => static::whatsappUrl($record->phone), shouldOpenInNewTab: true)
            ->visible(fn (HelpRequest $record) => static::whatsappUrl($record->phone) !== null);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('status')->badge(),
                TextColumn::make('type')->label('Need')->badge()->color('gray'),
                TextColumn::make('name')->searchable()->weight('semibold')
                    ->description(fn (HelpRequest $record) => str($record->details)->limit(80)),
                TextColumn::make('district')->placeholder('–')->toggleable(),
                TextColumn::make('reference')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Received')->since('Asia/Karachi')->sortable()
                    ->dateTimeTooltip('j M Y, g:i a', 'Asia/Karachi'),
            ])
            ->filters([
                SelectFilter::make('status')->options(HelpStatus::class)->multiple()
                    ->default([HelpStatus::New->value, HelpStatus::Reviewing->value, HelpStatus::Approved->value]),
                SelectFilter::make('type')->label('Need')->options(HelpType::class),
                SelectFilter::make('district')->options(fn () => array_combine(config('sswf.districts'), config('sswf.districts'))),
            ])
            ->recordActions([ViewAction::make(), static::updateStatusAction()]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['media', 'handledBy']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHelpRequests::route('/'),
            'view' => ViewHelpRequest::route('/{record}'),
        ];
    }
}
