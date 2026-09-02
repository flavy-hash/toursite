<?php

namespace App\Filament\Resources\Inquiries\Pages;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
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
                ->modalSubmitActionLabel('Yes, confirm booking')
                ->hidden(fn (): bool => $this->record->isBooked())
                ->action(function (): void {
                    $this->record->update(['status' => Inquiry::BOOKED]);
                    $this->refreshFormData(['status']);

                    Notification::make()
                        ->title('Booking confirmed')
                        ->success()
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
