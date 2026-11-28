<?php

namespace App\Filament\Resources\Donations\Tables;

use App\Enums\DonationStatus;
use App\Enums\PaymentMethod;
use App\Filament\Resources\Donations\DonationActions;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DonationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Search reference, donor or phone')
            ->emptyStateHeading('No donations found')
            ->emptyStateDescription('New donations appear here automatically. Try another tab or clear the search and filters.')
            ->columns([
                TextColumn::make('reference')->searchable()->copyable()->weight('semibold'),
                TextColumn::make('created_at')->label('Date (PKT)')->dateTime('j M Y, g:i a', 'Asia/Karachi')->sortable()->toggleable(),
                TextColumn::make('donor_name')->label('Donor')->searchable()
                    ->description(fn ($record) => $record->is_anonymous ? 'Anonymous on the site' : null),
                TextColumn::make('donor_phone')->label('Phone')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('amount')->label('Amount')->money('PKR', locale: 'en_PK', decimalPlaces: 0)->sortable()
                    ->summarize(Sum::make()->label('Total')->money('PKR', locale: 'en_PK', decimalPlaces: 0)),
                TextColumn::make('givingType.name')->label('Type')->sortable(),
                TextColumn::make('cause.name')->label('Cause')->placeholder('Where needed most')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('payment_method')->label('Method')->badge()->color('gray')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')->badge()->sortable(),
                IconColumn::make('needs_eligibility_review')->label('Review')->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')->trueColor('warning')->falseIcon('')
                    ->tooltip(fn ($record) => $record->needs_eligibility_review ? 'Zakat/Khums for a cause not marked eligible: contact the donor before use.' : null),
            ])
            ->filters([
                SelectFilter::make('status')->options(DonationStatus::class),
                SelectFilter::make('giving_type')->label('Type of giving')->relationship('givingType', 'name'),
                SelectFilter::make('cause')->relationship('cause', 'name'),
                SelectFilter::make('payment_method')->label('Method')->options(PaymentMethod::class),
                TernaryFilter::make('needs_eligibility_review')->label('Needs Zakat/Khums review'),
                Filter::make('date')
                    ->schema([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d)))
                    ->indicateUsing(fn (array $data) => array_filter([
                        ($data['from'] ?? null) ? 'From '.$data['from'] : null,
                        ($data['until'] ?? null) ? 'Until '.$data['until'] : null,
                    ])),
            ])
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make(DonationActions::all()),
            ]);
    }
}
