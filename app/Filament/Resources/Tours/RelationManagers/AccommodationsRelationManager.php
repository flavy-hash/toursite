<?php

namespace App\Filament\Resources\Tours\RelationManagers;

use App\Models\Accommodation;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Where a package stays. Properties are shared across packages, so they are
 * attached rather than owned — editing a lodge once updates every trip that
 * uses it.
 */
class AccommodationsRelationManager extends RelationManager
{
    protected static string $relationship = 'accommodations';

    protected static ?string $title = 'Where they stay';

    protected static ?string $recordTitleAttribute = 'name';

    /** Fields stored on the pivot, asked for when attaching. */
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nights')
                ->numeric()
                ->minValue(1)
                ->maxValue(30)
                ->helperText('How many nights of this trip are spent here.'),

            TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->helperText('Running order within the trip. Lower first.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('sort_order')
            ->emptyStateHeading('No accommodation attached yet')
            ->emptyStateDescription('Attach the lodges and camps this trip stays at.')
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('public')
                    ->imageHeight(40)
                    ->imageWidth(60)
                    ->extraImgAttributes(['class' => 'rounded-md object-cover']),

                TextColumn::make('name')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn (Accommodation $record) => $record->location)
                    ->wrap(),

                TextColumn::make('type')->badge()->color('gray'),

                TextColumn::make('level')
                    ->label('Standard')
                    ->badge()
                    ->formatStateUsing(fn (Accommodation $record) => $record->levelLabel()),

                TextColumn::make('pivot.nights')
                    ->label('Nights')
                    ->placeholder('—')
                    ->alignCenter(),

                TextColumn::make('pivot.sort_order')->label('Order')->alignCenter(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Attach accommodation')
                    ->preloadRecordSelect()
                    // Only offer properties that are actually live.
                    ->recordSelectOptionsQuery(fn ($query) => $query->published()->ordered())
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('nights')->numeric()->minValue(1)->maxValue(30),
                        TextInput::make('sort_order')->numeric()->default(0),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
