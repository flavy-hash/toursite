<?php

namespace App\Filament\Resources\TeamMembers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeamMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Who they are')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(120),

                    TextInput::make('role')
                        ->label('Job title')
                        ->maxLength(120)
                        ->placeholder('Head Guide'),

                    Textarea::make('bio')
                        ->label('Short introduction')
                        ->rows(4)
                        ->maxLength(600)
                        ->columnSpanFull()
                        ->helperText('A couple of sentences. Shown under their name on the team page.'),
                ]),

            Section::make('Photo')
                ->schema([
                    FileUpload::make('photo')
                        ->hiddenLabel()
                        ->image()
                        ->disk('public')
                        ->directory('team')
                        ->visibility('public')
                        ->imageEditor()
                        // The card crops to a tall portrait, so let staff frame
                        // the face themselves rather than trusting the centre.
                        ->imageCropAspectRatio('4:5')
                        ->maxSize(8192)
                        ->helperText('A head-and-shoulders portrait works best. Without one, their initials are shown instead.'),
                ]),

            Section::make('Contact')
                ->description('Both optional, and only shown on the site when filled in.')
                ->columns(2)
                ->schema([
                    TextInput::make('email')->email()->maxLength(160),
                    TextInput::make('phone')->tel()->maxLength(40),
                ]),

            Section::make('Order and visibility')
                ->columns(2)
                ->schema([
                    TextInput::make('sort_order')
                        ->label('Position')
                        ->numeric()
                        // No default: left empty, a new person goes to the end
                        // of the list rather than jumping to the top.
                        ->placeholder('Last')
                        ->helperText('Lower numbers come first. Drag the rows on the list instead if you prefer.'),

                    Toggle::make('is_published')
                        ->label('Show on the team page')
                        ->default(true),
                ]),
        ]);
    }
}
