<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\Page;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->columnSpanFull()->tabs([

                Tabs\Tab::make('Header')->schema([
                    Section::make('What visitors see first')
                        ->description('The banner across the top of the page.')
                        ->schema([
                            TextInput::make('title')
                                ->required()
                                ->maxLength(120)
                                ->helperText('Used in the browser tab, the breadcrumb, and as a fallback headline.'),

                            TextInput::make('eyebrow')
                                ->label('Small line above the headline')
                                ->maxLength(80)
                                ->placeholder('Who we are'),

                            TextInput::make('heading')
                                ->label('Headline')
                                ->maxLength(160)
                                ->helperText('Leave empty to use the title above.'),

                            Textarea::make('intro')
                                ->label('Opening paragraph')
                                ->rows(3)
                                ->maxLength(600),

                            FileUpload::make('hero_image')
                                ->label('Background photo')
                                ->image()
                                ->disk('public')
                                ->directory('pages')
                                ->visibility('public')
                                ->imageEditor()
                                ->maxSize(8192)
                                ->helperText('Landscape works best — it fills the width of the screen behind the headline.'),
                        ]),
                ]),

                Tabs\Tab::make('Content')->schema([
                    Section::make('Sections')
                        ->description('The body of the page. Each block becomes one section; add a photo and it sits alongside the text, alternating side each time.')
                        ->schema([
                            Repeater::make('sections')
                                ->hiddenLabel()
                                ->reorderable()
                                ->collapsible()
                                ->cloneable()
                                ->defaultItems(0)
                                ->addActionLabel('Add a section')
                                // Collapsed rows show their heading, so a long
                                // page stays readable when everything is shut.
                                ->itemLabel(fn (array $state): ?string => $state['heading'] ?? 'Untitled section')
                                ->schema([
                                    TextInput::make('heading')
                                        ->maxLength(160),

                                    Textarea::make('body')
                                        ->rows(6)
                                        ->helperText('Leave a blank line between paragraphs and they render as separate paragraphs.'),

                                    FileUpload::make('image')
                                        ->image()
                                        ->disk('public')
                                        ->directory('pages/sections')
                                        ->visibility('public')
                                        ->imageEditor()
                                        ->maxSize(8192)
                                        ->helperText('Optional. Without one, the section runs the full width of the page.'),
                                ]),
                        ]),
                ]),

                Tabs\Tab::make('Search & publishing')->schema([
                    Section::make('How this page appears in Google')
                        ->schema([
                            TextInput::make('meta_title')
                                ->label('Browser tab / search title')
                                ->maxLength(70)
                                ->helperText('Leave empty to use the page title. Around 60 characters shows in full.'),

                            Textarea::make('meta_description')
                                ->label('Search description')
                                ->rows(3)
                                ->maxLength(200)
                                ->helperText('Leave empty to use the opening paragraph. Around 155 characters shows in full.'),
                        ]),

                    Section::make('Publishing')->schema([
                        Toggle::make('is_published')
                            ->label('Visible on the site')
                            ->helperText(fn (?Page $record): string => $record?->path()
                                ? 'Turn this off and ' . $record->path() . ' returns "page not found".'
                                : 'Turn this off and the page returns "page not found".'),
                    ]),
                ]),
            ]),
        ]);
    }
}
