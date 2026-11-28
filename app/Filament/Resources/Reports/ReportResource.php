<?php

namespace App\Filament\Resources\Reports;

use App\Enums\ReportType;
use App\Filament\Resources\Reports\Pages\CreateReport;
use App\Filament\Resources\Reports\Pages\EditReport;
use App\Filament\Resources\Reports\Pages\ListReports;
use App\Models\Report;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/** Annual reports, audited accounts and certificates (plan 1.1, Transparency). */
class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Transparency';

    protected static ?string $navigationLabel = 'Reports & certificates';

    protected static ?string $modelLabel = 'document';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Document details')->columns(['default' => 1, 'md' => 3])->schema([
                Select::make('type')->options(ReportType::class)->required()->native(false)->live(),
                TextInput::make('year')->numeric()->minValue(2006)->maxValue(2100)
                    ->required(fn (Get $get) => $get('type') !== ReportType::Certificate->value
                        && $get('type') !== ReportType::Certificate),
                Toggle::make('is_published')->label('Shown on the Transparency page')->default(true)->inline(false),
                TextInput::make('title')->required()->maxLength(160)->columnSpanFull()
                    ->placeholder('Annual Report 2026'),
                Textarea::make('summary')->rows(2)->maxLength(400)->columnSpanFull()
                    ->helperText('Optional: one or two sentences about what the document covers.'),
                SpatieMediaLibraryFileUpload::make('file')->collection('file')->required()
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                    ->maxSize(20 * 1024)->downloadable()->columnSpanFull()
                    ->helperText('PDF preferred. This file is public: anyone can download it.'),
            ]),
            Section::make('Where each rupee goes')
                ->description('Spending by program for this year, from the audited accounts. The Transparency page shows the latest annual report’s breakdown as a chart. Leave empty to hide the chart.')
                ->visible(fn (Get $get) => in_array($get('type'), [ReportType::AnnualReport, ReportType::AnnualReport->value], true))
                ->schema([
                    Repeater::make('spending')->hiddenLabel()
                        ->schema([
                            TextInput::make('label')->label('Program or cost')->required()->placeholder('Clean Water'),
                            TextInput::make('amount')->label('Amount spent')->numeric()->minValue(0)->prefix('Rs')->required(),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Add a line')
                        ->reorderable(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->searchPlaceholder('Search document titles')
            ->emptyStateHeading('No documents found')
            ->emptyStateDescription('Upload an approved report or certificate, or clear the search and filters.')
            ->columns([
                TextColumn::make('title')->weight('semibold')->searchable()->wrap(),
                TextColumn::make('type')->badge(),
                TextColumn::make('year')->sortable()->placeholder('–'),
                ToggleColumn::make('is_published')->label('Published'),
            ])
            ->filters([
                SelectFilter::make('type')->options(ReportType::class),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReports::route('/'),
            'create' => CreateReport::route('/create'),
            'edit' => EditReport::route('/{record}/edit'),
        ];
    }
}
