<?php

namespace App\Filament\Resources\Faqs\Schemas;

use App\Models\Faq;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('The question')
                ->schema([
                    TextInput::make('question')
                        ->hiddenLabel()
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Do I need a visa to visit Tanzania?')
                        ->helperText('Write it the way a traveller would ask it — that is what they type into Google.'),
                ]),

            Section::make('The answer')
                ->schema([
                    Textarea::make('answer')
                        ->hiddenLabel()
                        ->rows(7)
                        // Not required outright: a question that came in from
                        // the website arrives with no answer, and that record
                        // has to be saveable. Publishing is what needs one.
                        ->requiredIf('is_published', true)
                        ->validationMessages([
                            'required_if' => 'Write an answer before putting this on the site.',
                        ])
                        ->helperText('Leave a blank line between paragraphs and they render as separate paragraphs.'),
                ]),

            // Only shown for questions that came in through the form on /faq.
            Section::make('Asked by a visitor')
                ->columns(3)
                ->visible(fn (?Faq $record): bool => (bool) $record?->wasAskedByAVisitor())
                ->schema([
                    Placeholder::make('asked_by_name_display')
                        ->label('Name')
                        ->content(fn (?Faq $record): string => $record?->asked_by_name ?: 'Not given'),

                    Placeholder::make('asked_by_email_display')
                        ->label('Email')
                        ->content(fn (?Faq $record): string => $record?->asked_by_email ?: 'Not given'),

                    Placeholder::make('asked_at_display')
                        ->label('Received')
                        ->content(fn (?Faq $record): string => $record?->asked_at
                            ? $record->asked_at->format('j M Y, H:i') . ' (' . $record->asked_at->diffForHumans() . ')'
                            : '—'),
                ]),

            Section::make('Grouping and order')
                ->columns(3)
                ->schema([
                    TextInput::make('category')
                        ->label('Section heading')
                        ->maxLength(80)
                        // Suggestions, not a fixed list: staff can type anything,
                        // and questions sharing a heading are grouped under it.
                        ->datalist(Faq::SUGGESTED_CATEGORIES)
                        ->placeholder(Faq::GENERAL)
                        ->helperText('Questions sharing a heading appear together. Leave empty for "' . Faq::GENERAL . '".'),

                    TextInput::make('sort_order')
                        ->label('Position')
                        ->numeric()
                        // No default: left empty, a new question goes to the
                        // end of the list rather than jumping to the top.
                        ->placeholder('Last')
                        ->helperText('Lower numbers come first. Dragging the rows on the list does the same thing.'),

                    Toggle::make('is_published')
                        ->label('Show on the FAQ page')
                        ->default(true),
                ]),
        ]);
    }
}
