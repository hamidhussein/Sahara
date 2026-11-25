<?php

namespace App\Filament\Resources\Programs\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->conversion('thumb')->label(''),
                TextColumn::make('name')->searchable()->weight('semibold'),
                TextColumn::make('projects_count')->counts('projects')->label('Projects'),
                TextColumn::make('language')->badge(),
                ToggleColumn::make('is_published')->label('Published'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
