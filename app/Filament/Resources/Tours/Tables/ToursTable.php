<?php

namespace App\Filament\Resources\Tours\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ToursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                // Resolved through the public disk rather than the image_url
                // accessor: that returns a root-relative path, which Filament
                // does not recognise as a URL and would try to re-resolve.
                ImageColumn::make('image')
                    ->label('Photo')
                    ->disk('public')
                    ->imageHeight(44)
                    ->imageWidth(66)
                    ->extraImgAttributes(['class' => 'rounded-md object-cover'])
                    ->defaultImageUrl(asset('/assets/images/carousel/lionss_with_her_cub.jpg')),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->tagline)
                    ->wrap(),

                TextColumn::make('category')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'Wildlife' => 'success',
                        'Mountain' => 'warning',
                        'Beach' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('days')->label('Duration'),

                TextColumn::make('price')->sortable(),

                TextColumn::make('rating')
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $state . ' (' . $record->reviews . ')'),

                IconColumn::make('is_published')->label('Live')->boolean()->sortable(),
                IconColumn::make('is_featured')->label('Featured')->boolean()->sortable(),

                TextColumn::make('updated_at')
                    ->dateTime('j M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')->options([
                    'Wildlife' => 'Wildlife',
                    'Mountain' => 'Mountain',
                    'Beach' => 'Beach',
                    'Cultural' => 'Cultural',
                ]),
                TernaryFilter::make('is_published')->label('Published'),
                TernaryFilter::make('is_featured')->label('Featured'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
