<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Enums\ProjectStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        $districts = array_combine(config('sswf.districts'), config('sswf.districts'));

        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->conversion('thumb')->label(''),
                TextColumn::make('name')->searchable()->weight('semibold')
                    ->description(fn ($record) => $record->place()),
                TextColumn::make('program.name')->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                IconColumn::make('is_featured')->label('Home')->boolean(),
                ToggleColumn::make('is_published')->label('Published'),
                TextColumn::make('updated_at')->since()->label('Updated')->sortable(),
            ])
            ->filters([
                SelectFilter::make('program')->relationship('program', 'name'),
                SelectFilter::make('district')->options($districts),
                SelectFilter::make('status')->options(ProjectStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
