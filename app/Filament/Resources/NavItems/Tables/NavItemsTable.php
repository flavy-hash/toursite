<?php

namespace App\Filament\Resources\NavItems\Tables;

use App\Models\NavItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NavItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->defaultGroup('location')
            ->columns([
                TextColumn::make('label')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (NavItem $record) => $record->path),

                TextColumn::make('location')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === NavItem::HEADER ? 'Header' : 'Bottom bar')
                    ->color(fn (string $state) => $state === NavItem::HEADER ? 'primary' : 'gray'),

                TextColumn::make('panel_heading')
                    ->label('Dropdown')
                    ->placeholder('Plain link')
                    ->description(fn (NavItem $record) => $record->hasPanel()
                        ? count($record->rail ?? []) . ' links'
                        : null)
                    ->wrap(),

                TextColumn::make('icon')->placeholder('—'),

                IconColumn::make('is_active')->label('Visible')->boolean()->sortable(),
            ])
            ->filters([
                SelectFilter::make('location')->options([
                    NavItem::HEADER => 'Header',
                    NavItem::BOTTOM => 'Bottom bar',
                ]),
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
