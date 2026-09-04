<?php

namespace App\Filament\Resources\Accommodations\Tables;

use App\Models\Accommodation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AccommodationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('image')
                    ->label('Photo')
                    ->disk('public')
                    ->imageHeight(44)
                    ->imageWidth(66)
                    ->extraImgAttributes(['class' => 'rounded-md object-cover']),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (Accommodation $record) => $record->location)
                    ->wrap(),

                TextColumn::make('type')->badge()->color('gray')->sortable(),

                TextColumn::make('level')
                    ->label('Standard')
                    ->badge()
                    ->formatStateUsing(fn (Accommodation $record) => $record->levelLabel())
                    ->color(fn (?string $state): string => match ($state) {
                        'luxury' => 'success',
                        'premium' => 'warning',
                        'classic' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('rating')
                    ->label('Stars')
                    ->formatStateUsing(fn (?int $state) => $state ? str_repeat('*', $state) : '—')
                    ->sortable(),

                TextColumn::make('price_impact')->label('Price note')->placeholder('—')->wrap(),

                TextColumn::make('tours_count')
                    ->label('Used by')
                    ->counts('tours')
                    ->formatStateUsing(fn (int $state) => $state . ' ' . str('package')->plural($state))
                    ->badge()
                    ->color(fn (int $state) => $state > 0 ? 'primary' : 'gray'),

                IconColumn::make('is_published')->label('Live')->boolean()->sortable(),
                IconColumn::make('is_featured')->label('Featured')->boolean()->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->options(Accommodation::TYPES),
                SelectFilter::make('level')->label('Standard')->options(Accommodation::LEVELS),
                SelectFilter::make('region')->options([
                    'northern' => 'Northern Circuit',
                    'southern' => 'Southern Circuit',
                    'kilimanjaro' => 'Kilimanjaro',
                    'zanzibar' => 'Zanzibar',
                ]),
                TernaryFilter::make('is_published')->label('Published'),
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
