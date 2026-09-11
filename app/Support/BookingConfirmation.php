<?php

namespace App\Support;

use App\Mail\BookingConfirmed;
use App\Models\Inquiry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * The one place a booking confirmation is sent.
 *
 * An enquiry reaches "booked" from several directions — the Confirm button on
 * the list, the one on the edit screen, the bulk action, and the status field
 * in the form itself. Keeping the send here means none of them can be the one
 * that forgets to tell the guest.
 */
class BookingConfirmation
{
    /**
     * Send the confirmation, unless this enquiry has already had one.
     *
     * Returns false when nothing was sent, whether because it was not needed
     * or because the mail server could not be reached. Never throws: a mail
     * failure must not undo a booking that staff have just confirmed, or throw
     * the admin panel to an error page.
     */
    public static function send(Inquiry $inquiry): bool
    {
        if (! self::isDue($inquiry)) {
            return false;
        }

        return self::dispatch($inquiry);
    }

    /**
     * Send again on request, even though one has already gone out.
     *
     * Used by the Resend action, for when a guest mistypes their address or
     * the first attempt failed and staff have since fixed the mail settings.
     */
    public static function resend(Inquiry $inquiry): bool
    {
        if (blank($inquiry->email)) {
            return false;
        }

        return self::dispatch($inquiry);
    }

    /** Whether this enquiry is booked and still owed a confirmation. */
    public static function isDue(Inquiry $inquiry): bool
    {
        return $inquiry->isBooked()
            && $inquiry->confirmation_sent_at === null
            && filled($inquiry->email);
    }

    private static function dispatch(Inquiry $inquiry): bool
    {
        try {
            Mail::to($inquiry->email)->queue(new BookingConfirmed($inquiry));
        } catch (Throwable $e) {
            /*
             * Left unstamped on purpose. confirmation_sent_at doubles as the
             * record of what the guest has actually been told, so a failed
             * send must stay visible in the panel as still outstanding.
             */
            Log::warning('Booking confirmation could not be sent.', [
                'inquiry' => $inquiry->id,
                'email' => $inquiry->email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        // Quietly, so stamping the record does not re-enter the observer that
        // called us in the first place.
        $inquiry->forceFill(['confirmation_sent_at' => now()])->saveQuietly();

        return true;
    }
}
