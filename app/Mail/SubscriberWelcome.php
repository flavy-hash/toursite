<?php

namespace App\Mail;

use App\Models\Subscriber;
use App\Models\Tour;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
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
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
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
