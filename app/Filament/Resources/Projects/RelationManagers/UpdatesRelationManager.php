<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Dated updates shown as a timeline on the project page,
 * e.g. "water samples taken", "plant inaugurated".
 */
class UpdatesRelationManager extends RelationManager
{
    protected static string $relationship = 'updates';

    protected static ?string $title = 'Updates';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('happened_on')->label('Date')->required()->default(now()),
                TextInput::make('title')->required()->maxLength(200)
                    ->placeholder('Construction in final stages'),
                Textarea::make('body')->label('Details')->rows(3)->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('happened_on', 'desc')
            ->columns([
                TextColumn::make('happened_on')->label('Date')->date('j M Y')->sortable(),
                TextColumn::make('title')->searchable(),
            ])
            ->headerActions([
                CreateAction::make()->label('Add update'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
