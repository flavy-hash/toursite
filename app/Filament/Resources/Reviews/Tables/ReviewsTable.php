<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Models\Review;
use App\Models\Tour;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('photo')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->imageHeight(40)
                    ->imageWidth(40)
                    ->defaultImageUrl(asset('/favicon-32x32.png')),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (Review $record) => $record->location),

                TextColumn::make('rating')
                    ->label('Stars')
                    ->badge()
                    ->formatStateUsing(fn (int $state) => str_repeat('*', $state))
                    ->color(fn (int $state) => $state >= 4 ? 'success' : ($state === 3 ? 'warning' : 'danger'))
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Review')
                    ->description(fn (Review $record) => str($record->body)->limit(70))
                    ->placeholder('None')
                    ->wrap(),

                TextColumn::make('tour_name')->label('Package')->placeholder('General')->wrap(),

                TextColumn::make('travelled_on')->label('Travelled')->date('M Y')->placeholder('Not given')->sortable(),

                IconColumn::make('is_published')->label('Live')->boolean()->sortable(),
                IconColumn::make('is_featured')->label('Featured')->boolean()->sortable(),

                TextColumn::make('source')->badge()->color('gray')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Published')
                    ->placeholder('All reviews')
                    ->trueLabel('Published only')
                    ->falseLabel('Awaiting approval'),

                TernaryFilter::make('is_featured')->label('Featured'),

                SelectFilter::make('rating')->options([5 => '5 stars', 4 => '4', 3 => '3', 2 => '2', 1 => '1']),

                SelectFilter::make('tour_slug')
                    ->label('Package')
                    ->options(fn () => Tour::pluck('name', 'slug')->all()),
            ])
            ->recordActions([
                self::approveAction(),
                self::unpublishAction(),

                ActionGroup::make([
                    self::featureAction(),
                    EditAction::make(),
                ])->label('More')->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::bulkPublishAction(true, 'Publish selected'),
                    self::bulkPublishAction(false, 'Unpublish selected'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /** Publishing puts someone's words on the public site, so it confirms first. */
    private static function approveAction(): Action
    {
        return Action::make('approve')
            ->label('Approve')
            ->icon('heroicon-m-check-badge')
            ->color('success')
            ->button()
            ->requiresConfirmation()
            ->modalHeading('Publish this review?')
            ->modalDescription(fn (Review $record) => 'It will appear on the reviews page under the name '
                . $record->name . '.')
            ->modalSubmitActionLabel('Yes, publish it')
            ->hidden(fn (Review $record) => $record->is_published)
            ->action(function (Review $record): void {
                $record->update(['is_published' => true]);

                Notification::make()->title('Review published')->success()->send();
            });
    }

    private static function unpublishAction(): Action
    {
        return Action::make('unpublish')
            ->label('Unpublish')
            ->icon('heroicon-m-eye-slash')
            ->color('gray')
            ->requiresConfirmation()
            ->hidden(fn (Review $record) => ! $record->is_published)
            ->action(function (Review $record): void {
                // Also drop it from the homepage; a hidden review must not
                // keep a featured slot it can no longer fill.
                $record->update(['is_published' => false, 'is_featured' => false]);

                Notification::make()->title('Review hidden')->success()->send();
            });
    }

    private static function featureAction(): Action
    {
        return Action::make('feature')
            ->label(fn (Review $record) => $record->is_featured ? 'Remove from homepage' : 'Feature on homepage')
            ->icon('heroicon-m-star')
            ->color('warning')
            // Only a published review can lead the homepage.
            ->hidden(fn (Review $record) => ! $record->is_published)
            ->action(function (Review $record): void {
                $record->update(['is_featured' => ! $record->is_featured]);

                Notification::make()
                    ->title($record->is_featured ? 'Featured on the homepage' : 'Removed from the homepage')
                    ->success()
                    ->send();
            });
    }

    private static function bulkPublishAction(bool $published, string $label): BulkAction
    {
        return BulkAction::make($published ? 'bulk_publish' : 'bulk_unpublish')
            ->label($label)
            ->icon($published ? 'heroicon-m-check-badge' : 'heroicon-m-eye-slash')
            ->color($published ? 'success' : 'gray')
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records) use ($published, $label): void {
                $records->each(fn (Review $review) => $review->update($published
                    ? ['is_published' => true]
                    : ['is_published' => false, 'is_featured' => false]));

                Notification::make()
                    ->title($label)
                    ->body($records->count() . ' reviews updated.')
                    ->success()
                    ->send();
            });
    }
}
