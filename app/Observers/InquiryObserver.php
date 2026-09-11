<?php

namespace App\Observers;

use App\Models\Inquiry;
use App\Support\BookingConfirmation;

class InquiryObserver
{
    /**
     * Tell the guest as soon as their enquiry is marked booked.
     *
     * Watching the record rather than the buttons means every route into
     * "booked" behaves the same — the Confirm action, the bulk action, and the
     * status dropdown on the edit form.
     */
    public function updated(Inquiry $inquiry): void
    {
        // Only on the transition. Saving a booked enquiry again — editing a
        // phone number, say — must not send a second confirmation.
        if (! $inquiry->wasChanged('status')) {
            return;
        }

        BookingConfirmation::send($inquiry);
    }
}
