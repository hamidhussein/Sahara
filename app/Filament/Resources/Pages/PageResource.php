<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Support\ContentFields;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Editable text pages, starting with the legal pages (plan 1.1). */
class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'title';

    /** Linked from the footer, so their web address must not change. */
    public const FIXED_SLUGS = ['privacy', 'refund-policy', 'terms'];

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(['default' => 1, 'lg' => 3])->components([
            Group::make([
                Section::make('Page content')->schema([
                    TextInput::make('title')->required()->maxLength(160),
                    TextInput::make('slug')->label('Web address')->prefix(url('/').'/')
                        ->alphaDash()->unique(ignoreRecord: true)
                        ->disabled(fn (?Page $record) => in_array($record?->slug, self::FIXED_SLUGS, true))
                        ->dehydrated(fn (?Page $record) => ! in_array($record?->slug, self::FIXED_SLUGS, true))
                        ->helperText(fn (?Page $record) => in_array($record?->slug, self::FIXED_SLUGS, true)
                            ? 'Fixed: the footer links to this address.'
                            : 'Filled from the title.'),
                    TextInput::make('lede')->label('One line under the title')->maxLength(200),
                    RichEditor::make('body')->label('Text')->required(),
                ]),
                ContentFields::seo(),
            ])->columnSpan(['default' => 1, 'lg' => 2]),
            Section::make('Publishing')->schema([
                Toggle::make('is_published')->label('Published')->default(true)
                    ->disabled(fn (?Page $record) => in_array($record?->slug, self::FIXED_SLUGS, true))
                    ->helperText(fn (?Page $record) => in_array($record?->slug, self::FIXED_SLUGS, true)
                        ? 'Legal pages stay published; edit the text instead.'
                        : null),
                ContentFields::language(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('title')
            ->columns([
                TextColumn::make('title')->weight('semibold')->searchable()
                    ->description(fn (Page $record) => '/'.$record->slug),
                TextColumn::make('updated_at')->label('Last updated')->since()->sortable(),
                ToggleColumn::make('is_published')->label('Published')
                    ->disabled(fn (Page $record) => in_array($record->slug, self::FIXED_SLUGS, true)),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
