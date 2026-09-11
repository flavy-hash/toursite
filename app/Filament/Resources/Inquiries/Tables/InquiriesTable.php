<?php

namespace App\Filament\Resources\Inquiries\Tables;

use App\Models\Inquiry;
use App\Models\Tour;
use App\Support\BookingConfirmation;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                    ->color(fn (?string $state) => Inquiry::statusColour($state))
                    // A booking the guest has not been told about needs chasing,
                    // so say so on the row rather than only inside the record.
                    ->description(fn (Inquiry $record) => match (true) {
                        $record->awaitsConfirmationEmail() => 'Not yet emailed',
                        $record->isBooked() => 'Confirmed '
                            . $record->confirmation_sent_at?->diffForHumans(),
                        default => null,
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options(Inquiry::statusOptions()),

                SelectFilter::make('tour_slug')
                    ->label('Package')
                    ->options(fn () => Tour::pluck('name', 'slug')->all()),
            ])
            ->recordActions([
                // Read the whole enquiry without entering an edit form.
                ViewAction::make(),

                // The common next step, one click from the list.
                self::confirmAction(),

                ActionGroup::make([
                    self::resendConfirmationAction(),
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
                . ($record->tour_name ?: 'their trip') . ' as booked, and emails '
                . ($record->email ?: 'them') . ' to say so.')
            ->modalSubmitActionLabel('Yes, confirm and notify')
            ->hidden(fn (Inquiry $record) => $record->isBooked())
            ->action(function (Inquiry $record): void {
                $record->update(['status' => Inquiry::BOOKED]);

                // The observer sends on the way through; re-read to find out
                // whether it got away, so the notification tells the truth.
                self::notifyOfConfirmation($record->fresh());
            });
    }

    /**
     * Staff need to know whether the guest was actually told, so a send that
     * failed is reported as plainly as one that worked.
     */
    private static function notifyOfConfirmation(Inquiry $record): void
    {
        if ($record->confirmationWasSent()) {
            Notification::make()
                ->title('Booking confirmed')
                ->body($record->name . ' is booked, and a confirmation email is on its way to '
                    . $record->email . '.')
                ->success()
                ->send();

            return;
        }

        Notification::make()
            ->title('Booked, but not emailed')
            ->body($record->name . ' is marked as booked, but the confirmation email could not '
                . 'be sent. Check your mail settings with "php artisan mail:test", then use '
                . 'Resend confirmation.')
            ->warning()
            ->persistent()
            ->send();
    }

    /**
     * For a guest who mistyped their address, or a confirmation that failed
     * when the mail server was briefly unreachable.
     */
    private static function resendConfirmationAction(): Action
    {
        return Action::make('resend_confirmation')
            ->label(fn (Inquiry $record) => $record->confirmationWasSent()
                ? 'Resend confirmation'
                : 'Send confirmation')
            ->icon('heroicon-m-envelope')
            ->color(fn (Inquiry $record) => $record->awaitsConfirmationEmail() ? 'warning' : 'gray')
            ->requiresConfirmation()
            ->modalHeading('Send the confirmation email again?')
            ->modalDescription(fn (Inquiry $record) => 'A booking confirmation will be sent to '
                . $record->email . '.')
            ->modalSubmitActionLabel('Send it')
            // Only meaningful once the booking is actually confirmed.
            ->hidden(fn (Inquiry $record) => ! $record->isBooked() || blank($record->email))
            ->action(function (Inquiry $record): void {
                if (BookingConfirmation::resend($record)) {
                    Notification::make()
                        ->title('Confirmation sent')
                        ->body('On its way to ' . $record->email . '.')
                        ->success()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Could not send')
                    ->body('The mail server did not accept it. Check your settings with '
                        . '"php artisan mail:test", then try again.')
                    ->danger()
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
            ->modalDescription($status === Inquiry::BOOKED
                ? 'Each guest will be emailed a booking confirmation.'
                : null)
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records) use ($status, $label): void {
                $records->each->update(['status' => $status]);

                $count = $records->count();
                $body = $count . ' ' . str('enquiry')->plural($count) . ' updated.';

                if ($status === Inquiry::BOOKED) {
                    // The observer sent as each record saved; re-read to report
                    // how many guests were actually reached.
                    $emailed = Inquiry::whereKey($records->modelKeys())
                        ->whereNotNull('confirmation_sent_at')
                        ->count();

                    $body .= ' ' . $emailed . ' of ' . $count . ' emailed.';

                    if ($emailed < $count) {
                        Notification::make()
                            ->title('Booked, but not everyone was emailed')
                            ->body($body . ' Check your mail settings with "php artisan mail:test", '
                                . 'then use Resend confirmation on the rows marked "Not yet emailed".')
                            ->warning()
                            ->persistent()
                            ->send();

                        return;
                    }
                }

                Notification::make()
                    ->title($label)
                    ->body($body)
                    ->success()
                    ->send();
            });
    }
}
