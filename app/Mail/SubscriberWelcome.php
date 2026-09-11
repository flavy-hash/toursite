<?php

namespace App\Mail;

use App\Models\Subscriber;
use App\Models\Tour;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * Sent once, when someone joins the list. Carries a few current packages so
 * the first email is worth opening rather than a bare acknowledgement.
 */
class SubscriberWelcome extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Subscriber $subscriber) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . config('site.brand.name') . ' — where to next?',

            // Same as the booking confirmation: a real reply address at our own
            // domain, which also reads better to a spam filter than a bare
            // no-reply broadcast.
            replyTo: [config('site.contact.email')],
        );
    }

    /**
     * Mail providers weigh this heavily when deciding whether bulk mail is
     * wanted: an unsubscribe the client can offer in its own UI, rather than
     * only a link buried in the footer. Gmail in particular treats its absence
     * as a spam signal on anything that looks like a newsletter.
     */
    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => '<' . $this->subscriber->unsubscribeUrl() . '>',
        ]);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            text: 'emails.text.welcome',
            with: [
                'tours' => Tour::published()
                    ->orderByDesc('is_featured')
                    ->ordered()
                    ->take(3)
                    ->get(),
            ],
        );
    }
}
