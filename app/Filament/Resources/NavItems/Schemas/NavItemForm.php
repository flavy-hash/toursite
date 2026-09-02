<?php

namespace App\Filament\Resources\NavItems\Schemas;

use App\Models\NavItem;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NavItemForm
{
    /** Icons available to the mobile tab bar, from the icon component. */
    private const ICONS = [
        'home' => 'Home',
        'compass' => 'Compass',
        'pin' => 'Map pin',
        'mountain' => 'Mountain',
        'wave' => 'Wave',
        'calendar' => 'Calendar',
        'info' => 'Info',
        'mail' => 'Mail',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('The link')
                ->description('What visitors see in the bar, and where it takes them.')
                ->columns(2)
                ->schema([
                    Select::make('location')
                        ->required()
                        ->default(NavItem::HEADER)
                        ->live()
                        ->options([
                            NavItem::HEADER => 'Header — the main bar on desktop',
                            NavItem::BOTTOM => 'Bottom bar — the phone tab bar',
                        ])
                        ->helperText('Header items can open a mega panel. Bottom bar items are icon and label only.'),

                    TextInput::make('label')
                        ->required()
                        ->maxLength(60)
                        ->helperText('Keep it short — the bar has limited room.'),

                    TextInput::make('path')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('/tours?region=kilimanjaro')
                        ->helperText('A path on this site, or a full https:// address.')
                        ->columnSpanFull(),

                    Select::make('icon')
                        ->options(self::ICONS)
                        ->visible(fn ($get) => $get('location') === NavItem::BOTTOM)
                        ->required(fn ($get) => $get('location') === NavItem::BOTTOM)
                        ->helperText('Shown above the label on phones.'),

                    Grid::make(2)->schema([
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers sit further left.'),

                        Toggle::make('is_active')
                            ->label('Visible')
                            ->default(true)
                            ->helperText('Turn off to hide without deleting.'),
                    ]),
                ]),

            // Header items only: a plain link such as Contact simply leaves
            // this empty, and no panel is rendered.
            Section::make('Dropdown panel')
                ->description('Optional. Fill in a heading and at least one rail link to give this item a dropdown; leave blank for a plain link.')
                ->visible(fn ($get) => $get('location') === NavItem::HEADER)
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('panel_heading')
                            ->label('Heading')
                            ->maxLength(120)
                            ->placeholder('Climbing the Roof of Africa'),

                        FileUpload::make('panel_image')
                            ->label('Panel photo')
                            ->image()
                            ->disk('public')
                            ->directory('nav')
                            ->visibility('public')
                            ->imageEditor()
                            ->openable()
                            ->maxSize(4096),
                    ]),

                    Textarea::make('panel_copy')
                        ->label('Description')
                        ->rows(3)
                        ->maxLength(400),

                    Grid::make(2)->schema([
                        TextInput::make('panel_cta_label')
                            ->label('Button text')
                            ->placeholder('Climb Kilimanjaro'),

                        TextInput::make('panel_cta_path')
                            ->label('Button link')
                            ->placeholder('/tours?region=kilimanjaro'),
                    ]),

                    Repeater::make('rail')
                        ->label('Menu links')
                        ->addActionLabel('Add a link')
                        ->reorderable()
                        ->collapsed(false)
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                        ->default([])
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('name')->required()->placeholder('Machame · 7 Days'),
                                TextInput::make('path')->required()->placeholder('/tours/kilimanjaro-machame'),
                            ]),
                        ]),
                ]),
        ]);
    }
}
