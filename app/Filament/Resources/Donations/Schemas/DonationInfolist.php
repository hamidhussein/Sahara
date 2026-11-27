<?php

namespace App\Filament\Resources\Donations\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DonationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Donation')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('reference')->copyable()->weight('bold'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('amount')->money('PKR', locale: 'en_PK', decimalPlaces: 0)->size('lg')->weight('bold'),
                        TextEntry::make('payment_method')->label('Method'),
                        TextEntry::make('givingType.name')->label('Type of giving'),
                        TextEntry::make('cause.name')->label('Cause')->placeholder('Where it is needed most'),
                        IconEntry::make('needs_eligibility_review')->label('Needs Zakat/Khums review')->boolean()
                            ->helperText('Restricted fund given for a cause not marked eligible. Contact the donor before using it.')
                            ->visible(fn ($record) => $record->needs_eligibility_review),
                        TextEntry::make('created_at')->label('Started')->dateTime('j M Y, g:i a'),
                        TextEntry::make('paid_at')->label('Paid')->dateTime('j M Y, g:i a')->placeholder('Not paid'),
                        TextEntry::make('confirmedBy.name')->label('Confirmed by')->placeholder('–'),
                        TextEntry::make('gateway_transaction_id')->label('Transaction ID')->placeholder('–')->copyable(),
                        TextEntry::make('receipt_sent_at')->label('Receipt emailed')->dateTime('j M Y, g:i a')->placeholder('Not sent'),
                    ]),
                Section::make('Donor')
                    ->schema([
                        TextEntry::make('donor_name')->label('Name'),
                        TextEntry::make('donor_phone')->label('Mobile')->copyable()
                            ->url(fn ($record) => 'tel:'.preg_replace('/[^0-9+]/', '', $record->donor_phone)),
                        TextEntry::make('donor_email')->label('Email')->placeholder('Not given')->copyable(),
                        IconEntry::make('is_anonymous')->label('Give anonymously')->boolean(),
                    ]),
                Section::make('Finance notes')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('finance_notes')->hiddenLabel()->placeholder('No notes yet.')->prose(),
                    ]),
            ]);
    }
}
