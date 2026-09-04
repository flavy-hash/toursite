<?php

namespace App\Filament\Resources\Accommodations\Schemas;

use App\Models\Accommodation;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AccommodationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->columnSpanFull()->tabs([

                Tabs\Tab::make('Details')->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(160)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, callable $set) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(160)
                            ->unique(ignoreRecord: true)
                            // Normalised on save. A slug with spaces or capitals
                            // cannot match the route and the page 404s.
                            ->dehydrateStateUsing(fn (?string $state) => Str::slug((string) $state)),

                        Select::make('type')
                            ->required()
                            ->options(Accommodation::TYPES)
                            ->default('Lodge'),

                        Select::make('level')
                            ->label('Standard')
                            ->required()
                            ->options(Accommodation::LEVELS)
                            ->default('mid-range')
                            ->helperText('Pairs with the tier on a package.'),

                        TextInput::make('location')
                            ->placeholder('Central Serengeti')
                            ->helperText('Where it actually is.'),

                        Select::make('region')
                            ->options([
                                'northern' => 'Northern Circuit',
                                'southern' => 'Southern Circuit',
                                'kilimanjaro' => 'Kilimanjaro',
                                'zanzibar' => 'Zanzibar',
                            ]),

                            

                        Select::make('rating')
                            ->label('Star rating')
                            ->options([5 => '5', 4 => '4', 3 => '3', 2 => '2', 1 => '1'])
                            ->placeholder('Not rated'),

                        Select::make('board_basis')
                            ->label('Board basis')
                            ->options(Accommodation::BOARD),

                        TextInput::make('price_impact')
                            ->label('Price note')
                            ->columnSpanFull()
                            ->placeholder('+$350 per person per night')
                            ->helperText('Shown exactly as typed — "included" and "on request" are fine.'),

                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull()
                            ->maxLength(1500),

                        TextInput::make('website')
                            ->url()
                            ->columnSpanFull()
                            ->placeholder('https://')
                            ->helperText("The property's own site, if it has one."),
                    ]),
                ]),

                Tabs\Tab::make('Imagery')->schema([
                    Section::make('Main photo')->schema([
                        FileUpload::make('image')
                            ->hiddenLabel()
                            ->image()
                            ->disk('public')
                            ->directory('accommodations')
                            ->visibility('public')
                            ->imageEditor()
                            ->openable()
                            ->maxSize(8192),
                    ]),

                    Section::make('Gallery')->schema([
                        FileUpload::make('gallery')
                            ->hiddenLabel()
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->disk('public')
                            ->directory('accommodations/gallery')
                            ->visibility('public')
                            ->imageEditor()
                            ->openable()
                            ->maxSize(8192)
                            ->panelLayout('grid'),
                    ]),
                ]),

                Tabs\Tab::make('Amenities')->schema([
                    Section::make()->schema([
                        TagsInput::make('amenities')
                            ->hiddenLabel()
                            ->placeholder('Add an amenity and press enter')
                            ->helperText('Pool, en-suite bathrooms, Wi-Fi, restaurant, game-viewing deck…')
                            ->default([]),
                    ]),
                ]),

                Tabs\Tab::make('Packages')->schema([
                    Section::make('Used by these packages')
                        ->description("Attach this property to the trips that stay here. It then appears in their \"Where you'll stay\" section.")
                        ->schema([
                            Select::make('tours')
                                ->hiddenLabel()
                                ->relationship('tours', 'name', fn ($query) => $query->orderBy('name'))
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->helperText('Nights and running order are set on each package, under "Where they stay".'),
                        ]),
                ]),

                Tabs\Tab::make('Publishing')->schema([
                    Section::make()->schema([
                        Grid::make(3)->schema([
                            Toggle::make('is_published')
                                ->label('Published')
                                ->default(true)
                                ->helperText('Unpublished properties are hidden from the site.'),

                            Toggle::make('is_featured')->label('Featured'),

                            TextInput::make('sort_order')
                                ->numeric()
                                ->default(0)
                                ->helperText('Lower numbers first.'),
                        ]),
                    ]),
                ]),
            ]),
        ]);
    }
}
