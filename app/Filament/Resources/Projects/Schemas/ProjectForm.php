<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Enums\ProjectStatus;
use App\Filament\Support\ContentFields;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        $districts = array_combine(config('sswf.districts'), config('sswf.districts'));

        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make()->schema([
                        ContentFields::title('name', 'Project name'),
                        ContentFields::slug('projects'),
                        Textarea::make('summary')->required()->rows(2)->maxLength(300)
                            ->helperText('One sentence for the project card.'),
                        RichEditor::make('story')
                            ->helperText('What the problem was, what was built and what changed. Add a quote from a local elder if you can.'),
                    ]),
                    Section::make('Key facts')
                        ->description('Leave a fact empty until it is confirmed. Empty facts are not shown on the site.')
                        ->columns(2)
                        ->schema([
                            Select::make('program_id')->label('Program')
                                ->relationship('program', 'name')->required()->preload()->native(false),
                            Select::make('status')->options(ProjectStatus::class)
                                ->default(ProjectStatus::Planned)->required()->native(false),
                            TextInput::make('village'),
                            Select::make('district')->options($districts)->native(false)->searchable(),
                            TextInput::make('donor')->placeholder('Gift of Mr Hadi Shahzad')->columnSpanFull(),
                            TextInput::make('people_served')->label('Number served')->numeric()->minValue(0),
                            TextInput::make('people_served_label')->label('What the number counts')->placeholder('Families served'),
                            TextInput::make('cost')->numeric()->minValue(0)->prefix('Rs'),
                            DatePicker::make('started_on')->label('Started'),
                            DatePicker::make('completed_on')->label('Completed or opened'),
                        ]),
                    ContentFields::seo(),
                ])->columnSpan(2),
                Group::make([
                    Section::make()->schema([
                        Toggle::make('is_published')->label('Published')->default(true),
                        Toggle::make('is_featured')->label('Show on the home page'),
                        ContentFields::language(),
                    ]),
                    Section::make('Photos')->schema([
                        ContentFields::cover(),
                        SpatieMediaLibraryFileUpload::make('before')->collection('before')->label('Before')->image()->maxSize(8 * 1024),
                        SpatieMediaLibraryFileUpload::make('after')->collection('after')->label('After')->image()->maxSize(8 * 1024),
                        ContentFields::gallery(),
                    ]),
                ]),
            ]);
    }
}
