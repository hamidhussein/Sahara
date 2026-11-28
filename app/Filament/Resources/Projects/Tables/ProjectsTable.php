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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        $districts = array_combine(config('sswf.districts'), config('sswf.districts'));

        return $table
            ->defaultSort('updated_at', 'desc')
            ->searchPlaceholder('Search project names')
            ->emptyStateHeading('No projects found')
            ->emptyStateDescription('Add a project to document your work, or clear the search and filters.')
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->conversion('thumb')->label('')
                    ->alt(fn ($record) => $record->name) // the thumbnail opens the project, so its link needs a name
                    ->disabledClick(fn ($record) => ! $record->hasMedia('cover')), // no photo: no empty link
                TextColumn::make('name')->searchable()->weight('semibold')->wrap()
                    ->description(fn ($record) => $record->place()),
                TextColumn::make('program.name')->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                IconColumn::make('is_featured')->label('Featured')->boolean()->toggleable(),
                ToggleColumn::make('is_published')->label('Published'),
                TextColumn::make('updated_at')->since()->label('Updated')->sortable()->toggleable()
                    ->dateTimeTooltip('j M Y, g:i a', 'Asia/Karachi'),
            ])
            ->filters([
                SelectFilter::make('program')->relationship('program', 'name'),
                SelectFilter::make('district')->options($districts),
                SelectFilter::make('status')->options(ProjectStatus::class),
                TernaryFilter::make('is_published')->label('Visibility')
                    ->trueLabel('Published')->falseLabel('Unpublished'),
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
