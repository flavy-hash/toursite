<?php

namespace App\Filament\Resources\Reviews\Schemas;

use App\Models\Tour;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Who')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->required()->maxLength(120),

                    TextInput::make('email')
                        ->email()
                        ->maxLength(180)
                        ->helperText('Never shown on the site.'),

                    TextInput::make('location')->placeholder('Nairobi, Kenya'),

                    Select::make('tour_slug')
                        ->label('Package')
                        ->options(fn () => Tour::pluck('name', 'slug')->all())
                        ->searchable()
                        ->helperText('Leave blank for a general review.'),

                    DatePicker::make('travelled_on')->label('Travelled'),

                    FileUpload::make('photo')
                        ->image()
                        ->disk('public')
                        ->directory('reviews')
                        ->visibility('public')
                        ->imageEditor()
                        ->maxSize(2048)
                        ->helperText('Optional. Falls back to their initials.'),
                ]),

            Section::make('The review')
                ->columns(3)
                ->schema([
                    TextInput::make('title')->label('Headline')->columnSpanFull()->maxLength(140),

                    Textarea::make('body')->label('Review')->rows(6)->required()->columnSpanFull(),

                    Select::make('rating')
                        ->label('Overall')
                        ->required()
                        ->options([5 => '5', 4 => '4', 3 => '3', 2 => '2', 1 => '1']),

                    Select::make('rating_guiding')
                        ->label('Guiding')
                        ->options([5 => '5', 4 => '4', 3 => '3', 2 => '2', 1 => '1']),

                    Select::make('rating_value')
                        ->label('Value')
                        ->options([5 => '5', 4 => '4', 3 => '3', 2 => '2', 1 => '1']),
                ]),

            Section::make('Publishing')
                ->columns(3)
                ->schema([
                    Toggle::make('is_published')
                        ->label('Published')
                        ->helperText('Submissions arrive unpublished until checked.'),

                    Toggle::make('is_featured')
                        ->label('Featured')
                        ->helperText('Featured reviews lead the homepage.'),

                    Select::make('source')
                        ->options([
                            'website' => 'Website form',
                            'google' => 'Google',
                            'tripadvisor' => 'TripAdvisor',
                            'sample' => 'Sample content',
                        ]),
                ]),
        ]);
    }
}
