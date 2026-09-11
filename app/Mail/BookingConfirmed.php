<?php

namespace App\Mail;

use App\Models\Inquiry;
use App\Models\Tour;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the guest when staff confirm their booking in the admin panel.
 *
 * Transactional, not marketing: it carries no unsubscribe link, because
 * someone who has just booked a trip must receive it whether or not they
 * are on the newsletter.
 */
class BookingConfirmed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Inquiry $inquiry) {}

    public function envelope(): Envelope
    {
        $trip = $this->inquiry->tour_name ?: 'your trip';

        return new Envelope(
            subject: 'Your booking is confirmed — ' . $trip,

            // Replies belong with the people who handled the booking.
            replyTo: [config('site.contact.email')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmed',
            text: 'emails.text.booking-confirmed',
            with: [
                // Only to deep-link the itinerary; the enquiry already records
                // the package name, so a retired or renamed tour is harmless.
                'tour' => $this->inquiry->tour_slug
                    ? Tour::published()->where('slug', $this->inquiry->tour_slug)->first()
                    : null,
            ],
        );
    }
}
