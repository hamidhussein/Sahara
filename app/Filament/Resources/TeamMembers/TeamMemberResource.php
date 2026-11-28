<?php

namespace App\Filament\Resources\TeamMembers;

use App\Enums\TeamGroup;
use App\Filament\Resources\TeamMembers\Pages\CreateTeamMember;
use App\Filament\Resources\TeamMembers\Pages\EditTeamMember;
use App\Filament\Resources\TeamMembers\Pages\ListTeamMembers;
use App\Models\TeamMember;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/** People on the Leadership page (plan 1.1). */
class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Leadership';

    protected static ?string $modelLabel = 'team member';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Team member details')->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(120),
                TextInput::make('role')->required()->maxLength(120)->placeholder('General Secretary'),
                Select::make('group')->label('Section')->options(TeamGroup::class)->required()->native(false)
                    ->default(TeamGroup::Executive),
                Toggle::make('is_published')->label('Shown on the Leadership page')->default(true)->inline(false)
                    ->helperText('Keep names hidden until they are confirmed.'),
                Textarea::make('bio')->label('Short bio')->rows(3)->maxLength(600)->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('photo')->collection('photo')->image()->imageEditor()
                    ->imageAspectRatio('1:1')->maxSize(4096)->columnSpanFull()
                    ->helperText('A square, recent photo. Without one, initials are shown.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->searchPlaceholder('Search team member names')
            ->emptyStateHeading('No team members found')
            ->emptyStateDescription('Add a confirmed team member, or clear the search and section filter.')
            ->reorderable('sort_order')
            ->defaultGroup('group')
            ->columns([
                SpatieMediaLibraryImageColumn::make('photo')->collection('photo')->conversion('thumb')->circular()->label(''),
                TextColumn::make('name')->weight('semibold')->searchable()->wrap(),
                TextColumn::make('role')->wrap(),
                ToggleColumn::make('is_published')->label('Shown'),
            ])
            ->filters([
                SelectFilter::make('group')->label('Section')->options(TeamGroup::class),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeamMembers::route('/'),
            'create' => CreateTeamMember::route('/create'),
            'edit' => EditTeamMember::route('/{record}/edit'),
        ];
    }
}
