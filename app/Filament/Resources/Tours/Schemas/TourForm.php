<?php

namespace App\Filament\Resources\Tours\Schemas;

use App\Support\DocumentParagraphs;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class TourForm
{
    /**
     * @param  string|null  $lockedRegion  Set by the per-region resources, where
     *                                     the region is implied by the section
     *                                     you are working in and must not drift.
     */
    public static function configure(Schema $schema, ?string $lockedRegion = null): Schema
    {
        return $schema->components([
            Tabs::make()->columnSpanFull()->tabs([

                Tabs\Tab::make('Details')->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(160)
                            ->live(onBlur: true)
                            // Only auto-fill the slug while creating; changing it
                            // later would break links already in the wild.
                            ->afterStateUpdated(function (string $operation, $state, callable $set) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(160)
                            ->unique(ignoreRecord: true)
                            ->helperText('Used in the URL: /tours/your-slug'),

                        TextInput::make('tagline')
                            ->maxLength(200)
                            ->columnSpanFull()
                            ->helperText('One line under the title on the package page.'),

                        Select::make('category')
                            ->required()
                            ->options([
                                'Wildlife' => 'Wildlife',
                                'Mountain' => 'Mountain',
                                'Beach' => 'Beach',
                                'Cultural' => 'Cultural',
                            ])
                            ->helperText('Drives the filter chips on /tours.'),

                        Select::make('difficulty')
                            ->required()
                            ->default('Easy')
                            ->options([
                                'Easy' => 'Easy',
                                'Moderate' => 'Moderate',
                                'Challenging' => 'Challenging',
                            ]),

                        Select::make('region')
                            ->options([
                                'northern' => 'Northern Circuit',
                                'southern' => 'Southern Circuit',
                                'kilimanjaro' => 'Kilimanjaro',
                                'zanzibar' => 'Zanzibar',
                            ])
                            ->default($lockedRegion)
                            // Fixed inside a region section, but still submitted
                            // so the package lands where it was created.
                            ->disabled(filled($lockedRegion))
                            ->dehydrated()
                            ->helperText($lockedRegion
                                ? 'Set by the section you are working in.'
                                : 'Drives the region links in the site navigation.'),

                        Select::make('tier')
                            ->options([
                                'budget' => 'Budget',
                                'classic' => 'Classic',
                                'mid-range' => 'Mid-range',
                                'luxury' => 'Luxury',
                            ]),
                    ]),

                    Section::make('Facts')->columns(3)->schema([
                        TextInput::make('days')->required()->placeholder('7 Days'),
                        TextInput::make('nights')->placeholder('6 Nights'),
                        TextInput::make('group')->label('Group size')->placeholder('Max 6'),
                        TextInput::make('location')->placeholder('Northern Circuit, Tanzania'),
                        TextInput::make('best_time')->label('Best time')->placeholder('June – October'),
                        TextInput::make('start')->label('Starts in')->placeholder('Arusha'),
                        TextInput::make('end')->label('Ends in')->placeholder('Arusha'),
                    ]),

                    Section::make('Price & rating')->columns(3)->schema([
                        TextInput::make('price')
                            ->required()
                            ->placeholder('$2,450')
                            ->helperText('Shown exactly as typed.'),

                        TextInput::make('price_note')
                            ->columnSpan(2)
                            ->placeholder('per person sharing, excluding international flights'),

                        TextInput::make('rating')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5)
                            ->step(0.1)
                            ->default(5.0),

                        TextInput::make('reviews')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('highlight')
                            ->label('Pull quote')
                            ->placeholder('Witnessing a cheetah hunt at sunrise')
                            ->helperText('The italic line on the card.'),
                    ]),
                ]),

                Tabs\Tab::make('Imagery')->schema([
                    Section::make('Hero photo')
                        ->description('The full-width photo behind the title at the top of the package page.')
                        ->schema([
                            FileUpload::make('image')
                                ->label('Hero photo')
                                ->image()
                                ->disk('public')
                                ->directory('tours')
                                ->visibility('public')
                                ->imageEditor()
                                ->openable()
                                ->downloadable()
                                ->maxSize(8192)
                                ->required()
                                // A filled single-file panel has nowhere to drop
                                // a new file, so give it an explicit way out.
                                ->hintAction(
                                    Action::make('replaceImage')
                                        ->label('Replace')
                                        ->icon(Heroicon::ArrowPath)
                                        ->visible(fn ($state): bool => filled($state))
                                        ->action(fn (callable $set) => $set('image', null))
                                )
                                ->helperText('Landscape works best — it is cropped to the full width of the screen. Max 8 MB. To use a different one, press "Replace" above the box.'),
                        ]),

                    Section::make('Gallery')
                        ->description('The "Gallery" grid further down the package page. Drag to reorder — that is the order visitors see.')
                        ->schema([
                            FileUpload::make('gallery')
                                ->hiddenLabel()
                                ->image()
                                ->multiple()
                                ->reorderable()
                                ->appendFiles()
                                ->disk('public')
                                ->directory('tours/gallery')
                                ->visibility('public')
                                ->imageEditor()
                                ->openable()
                                ->downloadable()
                                ->maxSize(8192)
                                ->panelLayout('grid')
                                ->helperText('You can select several at once. The gallery section only appears on the site once there is at least one photo.'),
                        ]),
                ]),

                Tabs\Tab::make('Content')->schema([
                    Section::make('Overview')->schema([
                        // Type the overview, or lift it out of a Word file.
                        FileUpload::make('summary_document')
                            ->label('Import from a document')
                            ->helperText('Optional. Upload a .docx or .txt and each paragraph becomes an entry below, replacing what is there. You can edit them afterwards.')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'text/plain',
                                'text/markdown',
                            ])
                            ->maxSize(4096)
                            // Never persisted: the file is only a delivery
                            // mechanism for the text, so it is not a tour column.
                            ->storeFiles(false)
                            ->dehydrated(false)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $file = is_array($state) ? reset($state) : $state;

                                if (! $file instanceof TemporaryUploadedFile) {
                                    return;
                                }

                                $paragraphs = DocumentParagraphs::fromFile(
                                    $file->getRealPath(),
                                    $file->getClientOriginalExtension(),
                                );

                                if ($paragraphs === []) {
                                    Notification::make()
                                        ->title('No text found')
                                        ->body('That file had no readable paragraphs. A legacy .doc must be saved as .docx first.')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                $set('summary', $paragraphs);

                                Notification::make()
                                    ->title(count($paragraphs) . ' ' . str('paragraph')->plural(count($paragraphs)) . ' imported')
                                    ->success()
                                    ->send();
                            }),

                        Repeater::make('summary')
                            ->label('Paragraphs')
                            ->simple(Textarea::make('paragraph')->rows(4)->required())
                            ->addActionLabel('Add paragraph')
                            ->reorderable()
                            ->default([]),

                        TagsInput::make('highlights')
                            ->label('Highlights')
                            ->placeholder('Add a highlight and press enter')
                            ->default([]),
                    ]),

                    Section::make('Itinerary')->schema([
                        Repeater::make('itinerary')
                            ->hiddenLabel()
                            ->addActionLabel('Add a day')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => filled($state['title'] ?? null)
                                ? 'Day ' . ($state['day'] ?? '?') . ' — ' . $state['title']
                                : null)
                            ->default([])
                            ->schema([
                                Grid::make(4)->schema([
                                    TextInput::make('day')->numeric()->required()->columnSpan(1),
                                    TextInput::make('title')->required()->columnSpan(3),
                                ]),
                                Textarea::make('copy')->label('Description')->rows(3)->required(),
                                Grid::make(2)->schema([
                                    TextInput::make('stay')->placeholder('Serengeti camp'),
                                    TextInput::make('meals')->placeholder('Breakfast, lunch, dinner'),
                                ]),
                            ]),
                    ]),

                    Section::make('Inclusions')->columns(2)->schema([
                        TagsInput::make('included')
                            ->label("What's included")
                            ->placeholder('Add an item and press enter')
                            ->default([]),

                        TagsInput::make('excluded')
                            ->label('Not included')
                            ->placeholder('Add an item and press enter')
                            ->default([]),
                    ]),
                ]),

                Tabs\Tab::make('Publishing')->schema([
                    Section::make()->columns(3)->schema([
                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true)
                            ->helperText('Unpublished packages 404 on the public site.'),

                        Toggle::make('is_featured')
                            ->label('Featured')
                            ->helperText('Featured packages lead the homepage.'),

                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers first.'),
                    ]),
                ]),
            ]),
        ]);
    }
}
