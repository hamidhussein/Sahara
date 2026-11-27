<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\ContentLanguage;
use App\Enums\PostCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->conversion('thumb')->label(''),
                TextColumn::make('title')->searchable()->weight('semibold')->wrap(),
                TextColumn::make('category')->badge(),
                TextColumn::make('language')->badge(),
                TextColumn::make('published_at')->label('Published')->dateTime('j M Y, g:i a')->sortable()
                    ->placeholder('Draft'),
            ])
            ->filters([
                SelectFilter::make('category')->options(PostCategory::class),
                SelectFilter::make('language')->options(ContentLanguage::class),
                TernaryFilter::make('published')->label('Status')
                    ->placeholder('All')->trueLabel('Published')->falseLabel('Drafts')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('published_at'),
                        false: fn (Builder $query) => $query->whereNull('published_at'),
                    ),
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
