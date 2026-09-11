<?php

namespace App\Mail;

use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * A broadcast about one or more packages, composed in the admin panel.
 *
 * Each subscriber gets their own message rather than a shared BCC, so the
 * unsubscribe link can be signed for that individual address.
 *
 * @property Collection<int, \App\Models\Tour> $tours
 */
class PackageNewsletter extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Subscriber $subscriber,
        public string $subjectLine,
        public ?string $intro,
        public Collection $tours,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
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
            view: 'emails.newsletter',
            text: 'emails.text.newsletter',
        );
    }
}
