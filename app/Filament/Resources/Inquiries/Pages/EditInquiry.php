<?php

namespace App\Filament\Resources\Inquiries\Pages;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
use App\Support\BookingConfirmation;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInquiry extends EditRecord
{
    protected static string $resource = InquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Same one-click confirm as the list, so staff never have to hunt
            // for the status dropdown to record a booking.
            Action::make('confirm')
                ->label('Confirm booking')
                ->icon(Inquiry::STATUSES[Inquiry::BOOKED]['icon'])
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Confirm this booking?')
                ->modalDescription(fn (): string => 'This emails '
                    . ($this->record->email ?: 'the guest') . ' to say their booking is confirmed.')
                ->modalSubmitActionLabel('Yes, confirm and notify')
                ->hidden(fn (): bool => $this->record->isBooked())
                ->action(function (): void {
                    $this->record->update(['status' => Inquiry::BOOKED]);
                    $this->refreshFormData(['status']);

                    // The observer sent on the way through; report what happened.
                    if ($this->record->fresh()?->confirmationWasSent()) {
                        Notification::make()
                            ->title('Booking confirmed')
                            ->body('A confirmation email is on its way to ' . $this->record->email . '.')
                            ->success()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Booked, but not emailed')
                        ->body('The confirmation email could not be sent. Check your mail settings '
                            . 'with "php artisan mail:test", then use Resend confirmation.')
                        ->warning()
                        ->persistent()
                        ->send();
                }),

            Action::make('resend_confirmation')
                ->label('Resend confirmation')
                ->icon('heroicon-m-envelope')
                ->color(fn (): string => $this->record->awaitsConfirmationEmail() ? 'warning' : 'gray')
                ->requiresConfirmation()
                ->modalHeading('Send the confirmation email again?')
                ->modalDescription(fn (): string => 'A booking confirmation will be sent to '
                    . $this->record->email . '.')
                ->modalSubmitActionLabel('Send it')
                ->hidden(fn (): bool => ! $this->record->isBooked() || blank($this->record->email))
                ->action(function (): void {
                    if (BookingConfirmation::resend($this->record)) {
                        Notification::make()
                            ->title('Confirmation sent')
                            ->body('On its way to ' . $this->record->email . '.')
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
                }),

            Action::make('contacted')
                ->label('Mark as contacted')
                ->icon(Inquiry::STATUSES[Inquiry::CONTACTED]['icon'])
                ->color('info')
                ->hidden(fn (): bool => $this->record->status !== Inquiry::NEW)
                ->action(function (): void {
                    $this->record->update(['status' => Inquiry::CONTACTED]);
                    $this->refreshFormData(['status']);

                    Notification::make()->title('Marked as contacted')->success()->send();
                }),

            DeleteAction::make(),
        ];
    }
}
