<?php

namespace App\Filament\Resources\Programs\Schemas;

use App\Filament\Support\ContentFields;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                Group::make([
                    Section::make('Program details')->schema([
                        ContentFields::title('name', 'Program name'),
                        ContentFields::slug('programs'),
                        TextInput::make('tagline')->maxLength(160)
                            ->placeholder('Safe drinking water for every home.'),
                        Textarea::make('summary')->required()->rows(3)->maxLength(400)
                            ->helperText('One or two sentences, shown on program cards and the home page.'),
                        RichEditor::make('body')->label('Full description')
                            ->helperText('The problem, what we do, highlights and how a gift helps.'),
                    ]),
                    ContentFields::seo(),
                ])->columnSpan(['default' => 1, 'lg' => 2]),
                Group::make([
                    Section::make('Publishing')->schema([
                        Toggle::make('is_published')->label('Published')->default(true),
                        ContentFields::language(),
                        TextInput::make('sort_order')->label('Order in menus')->numeric()->default(0),
                    ]),
                    Section::make('Photos')->schema([
                        ContentFields::cover(),
                        ContentFields::gallery(),
                    ]),
                ]),
            ]);
    }
}
