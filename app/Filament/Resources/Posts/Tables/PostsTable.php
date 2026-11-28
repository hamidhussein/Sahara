<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\ContentLanguage;
use App\Enums\PostCategory;
use App\Models\Post;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->searchPlaceholder('Search news titles')
            ->emptyStateHeading('No news posts found')
            ->emptyStateDescription('Create your first field update, or clear the search and filters to see more posts.')
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->conversion('thumb')->label(''),
                TextColumn::make('title')->searchable()->weight('semibold')->wrap(),
                TextColumn::make('category')->badge(),
                TextColumn::make('language')->badge(),
                TextColumn::make('publication_status')->label('Status')->badge()
                    ->state(fn (Post $record): string => match (true) {
                        $record->published_at === null => 'Draft',
                        $record->published_at->isFuture() => 'Scheduled',
                        default => 'Published',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Published' => 'success',
                        'Scheduled' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('published_at')->label('Publish date (PKT)')->dateTime('j M Y, g:i a', 'Asia/Karachi')->sortable()
                    ->placeholder('Not scheduled')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('category')->options(PostCategory::class),
                SelectFilter::make('language')->options(ContentLanguage::class),
                SelectFilter::make('publication_status')->label('Status')
                    ->options(['draft' => 'Draft', 'scheduled' => 'Scheduled', 'published' => 'Published'])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'draft' => $query->whereNull('published_at'),
                        'scheduled' => $query->where('published_at', '>', now()),
                        'published' => $query->published(),
                        default => $query,
                    }),
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
