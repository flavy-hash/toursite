<?php

namespace App\Filament\Resources\Inquiries\Pages;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

/**
 * Read-only view of one enquiry.
 *
 * A page rather than a modal: enquiries carry a free-text message that can run
 * long, and a page gives staff a URL they can share with a colleague.
 */
class ViewInquiry extends ViewRecord
{
    protected static string $resource = InquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
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

                    Notification::make()->title('Booking confirmed')->success()->send();
                }),

            Action::make('reply')
                ->label('Reply by email')
                ->icon('heroicon-m-envelope')
                ->color('gray')
                ->url(fn (): string => 'mailto:' . $this->record->email
                    . '?subject=' . rawurlencode('Re: your enquiry about '
                        . ($this->record->tour_name ?: 'your trip')))
                ->openUrlInNewTab(),

            EditAction::make(),
        ];
    }
}
