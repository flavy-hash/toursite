<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Models\Page;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('hero_image')
                    ->label('Banner')
                    ->disk('public')
                    ->imageHeight(48)
                    ->defaultImageUrl(fn () => null),

                TextColumn::make('title')->weight('semibold')->searchable(),

                // The URL matters more than the slug here — staff think in
                // pages they can visit, not database keys.
                TextColumn::make('slug')
                    ->label('Address')
                    ->formatStateUsing(fn (Page $record): string => $record->path() ?? '—')
                    ->color('gray'),

                TextColumn::make('sections')
                    ->label('Sections')
                    ->formatStateUsing(fn (?array $state): string => (string) count($state ?? []))
                    ->alignCenter(),

                IconColumn::make('is_published')->label('Live')->boolean(),

                TextColumn::make('updated_at')
                    ->label('Last edited')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),

                // Straight to the page as a visitor sees it.
                Action::make('view_live')
                    ->label('View')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Page $record): ?string => $record->url())
                    ->hidden(fn (Page $record): bool => ! $record->url())
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}
