<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\PostCategory;
use App\Filament\Support\ContentFields;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make()->schema([
                        ContentFields::title(),
                        ContentFields::slug('news'),
                        Textarea::make('excerpt')->label('Short summary')->rows(2)->maxLength(300)
                            ->helperText('Shown on news cards. Leave empty to use the start of the article.'),
                        RichEditor::make('body')->label('Article')->required(),
                    ]),
                    ContentFields::seo(),
                ])->columnSpan(2),
                Group::make([
                    Section::make()->schema([
                        DateTimePicker::make('published_at')->label('Publish on')
                            ->native(false)->seconds(false)
                            ->helperText('Leave empty to keep this as a draft. A future date publishes it then.'),
                        Select::make('category')->options(PostCategory::class)->required()->native(false),
                        ContentFields::language(),
                        Toggle::make('is_featured')->label('Feature at the top of News'),
                    ]),
                    Section::make('Photos')->schema([
                        ContentFields::cover(),
                        ContentFields::gallery(),
                    ]),
                ]),
            ]);
    }
}
