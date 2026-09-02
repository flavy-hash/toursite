<?php

namespace App\Filament\Resources\Inquiries\Tables;

use App\Models\Inquiry;
use App\Models\Tour;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class InquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Newest first — this is a work queue, not a catalogue.
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('j M, H:i')
                    ->description(fn (Inquiry $record) => $record->created_at?->diffForHumans())
                    ->sortable(),

                TextColumn::make('name')->searchable()->sortable()->weight('semibold'),

                TextColumn::make('email')->searchable()->copyable()->copyMessage('Email copied'),

                TextColumn::make('phone')->searchable()->copyable()->placeholder('—'),

                TextColumn::make('tour_name')
                    ->label('Package')
                    ->searchable()
                    ->placeholder('General enquiry')
                    ->wrap(),

                TextColumn::make('travel_date')
                    ->label('Departure')
                    ->date('j M Y')
                    ->placeholder('Flexible')
                    ->sortable(),

                TextColumn::make('travellers')->label('Pax')->alignCenter(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state) => Inquiry::statusLabel($state))
                    ->color(fn (?string $state) => Inquiry::statusColour($state)),
            ])
            ->filters([
                SelectFilter::make('status')->options(Inquiry::statusOptions()),

                SelectFilter::make('tour_slug')
                    ->label('Package')
                    ->options(fn () => Tour::pluck('name', 'slug')->all()),
            ])
            ->recordActions([
                // The common next step, one click from the list.
                self::confirmAction(),

                ActionGroup::make([
                    self::statusAction(Inquiry::CONTACTED, 'Mark as contacted'),
                    self::statusAction(Inquiry::QUOTED, 'Mark as quoted'),
                    self::statusAction(Inquiry::CLOSED, 'Close enquiry'),
                    self::statusAction(Inquiry::NEW, 'Reopen as new'),
                    EditAction::make(),
                ])->label('More')->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::bulkStatusAction(Inquiry::CONTACTED, 'Mark as contacted'),
                    self::bulkStatusAction(Inquiry::BOOKED, 'Confirm as booked'),
                    self::bulkStatusAction(Inquiry::CLOSED, 'Close'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Confirming a booking is the decision that matters, so it asks first and
     * disappears once the enquiry is already booked.
     */
    private static function confirmAction(): Action
    {
        return Action::make('confirm')
            ->label('Confirm')
            ->icon(Inquiry::STATUSES[Inquiry::BOOKED]['icon'])
            ->color('success')
            ->button()
            ->requiresConfirmation()
            ->modalHeading('Confirm this booking?')
            ->modalDescription(fn (Inquiry $record) => 'This marks '
                . $record->name . '&rsquo;s enquiry about '
                . ($record->tour_name ?: 'their trip') . ' as booked.')
            ->modalSubmitActionLabel('Yes, confirm booking')
            ->hidden(fn (Inquiry $record) => $record->isBooked())
            ->action(function (Inquiry $record): void {
                $record->update(['status' => Inquiry::BOOKED]);

                Notification::make()
                    ->title('Booking confirmed')
                    ->body($record->name . ' is now marked as booked.')
                    ->success()
                    ->send();
            });
    }

    private static function statusAction(string $status, string $label): Action
    {
        return Action::make('status_' . $status)
            ->label($label)
            ->icon(Inquiry::STATUSES[$status]['icon'])
            ->color(Inquiry::statusColour($status))
            ->hidden(fn (Inquiry $record) => $record->status === $status)
            ->action(function (Inquiry $record) use ($status, $label): void {
                $record->update(['status' => $status]);

                Notification::make()
                    ->title($label)
                    ->body($record->name . ' is now ' . Inquiry::statusLabel($status) . '.')
                    ->success()
                    ->send();
            });
    }

    private static function bulkStatusAction(string $status, string $label): BulkAction
    {
        return BulkAction::make('bulk_' . $status)
            ->label($label)
            ->icon(Inquiry::STATUSES[$status]['icon'])
            ->color(Inquiry::statusColour($status))
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records) use ($status, $label): void {
                $records->each->update(['status' => $status]);

                Notification::make()
                    ->title($label)
                    ->body($records->count() . ' ' . str('enquiry')->plural($records->count()) . ' updated.')
                    ->success()
                    ->send();
            });
    }
}
