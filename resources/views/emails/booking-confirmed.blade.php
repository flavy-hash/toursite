@php
    $trip = $inquiry->tour_name ?: 'your trip';
    $whatsapp = 'https://wa.me/' . config('site.contact.whatsapp');
@endphp

{{-- No unsubscribe link: this is transactional. See emails/layout.blade.php. --}}
<x-mail-layout title="Booking confirmed">
    <p style="margin:0 0 16px; font-size:22px; color:#3a2418;">
        Your booking is confirmed
    </p>

    <p style="margin:0 0 18px; font-size:15px; line-height:1.7; color:#6b5a49;">
        Hi {{ $inquiry->name }}, good news &mdash; we&rsquo;ve confirmed your place on
        <strong style="color:#3a2418;">{{ $trip }}</strong>. Everything below is what we
        have on file. If any of it looks wrong, just reply to this email and we&rsquo;ll
        put it right.
    </p>

    <div style="margin:26px 0; padding:20px 22px; background:#faf6ee; border-radius:14px;">
        <p style="margin:0 0 14px; font-size:11px; letter-spacing:2px; text-transform:uppercase; color:#a08f7c;">
            Your booking
        </p>

        @php
            $details = array_filter([
                'Trip' => $inquiry->tour_name,
                'Departure' => $inquiry->travel_date?->format('j F Y'),
                'Travellers' => $inquiry->travellers,
                'Reference' => 'TA-' . str_pad((string) $inquiry->id, 5, '0', STR_PAD_LEFT),
            ]);
        @endphp

        @foreach ($details as $label => $value)
            <p style="margin:0 0 8px; font-size:15px; line-height:1.6; color:#6b5a49;">
                <span style="display:inline-block; min-width:110px; color:#a08f7c;">{{ $label }}</span>
                <strong style="color:#3a2418;">{{ $value }}</strong>
            </p>
        @endforeach

        @unless ($inquiry->travel_date)
            <p style="margin:10px 0 0; font-size:14px; line-height:1.6; color:#8a7a68;">
                We haven&rsquo;t fixed a departure date yet &mdash; we&rsquo;ll be in touch
                to settle that with you.
            </p>
        @endunless
    </div>

    <p style="margin:0 0 10px; font-size:11px; letter-spacing:2px; text-transform:uppercase; color:#a08f7c;">
        What happens next
    </p>

    <p style="margin:0 0 18px; font-size:15px; line-height:1.7; color:#6b5a49;">
        One of our guides will contact you shortly with the full day-by-day plan,
        what to pack, and how to settle the balance. Nothing is needed from you
        right now.
    </p>

    @if ($tour)
        <p style="margin:26px 0 0;">
            <a href="{{ route('tours.show', $tour->slug) }}"
               style="display:inline-block; padding:13px 26px; background:#3a2418; color:#f0e6d2;
                      font-size:14px; letter-spacing:1px; text-decoration:none; border-radius:999px;">
                View your itinerary
            </a>
        </p>
    @endif

    <p style="margin:28px 0 0; font-size:15px; line-height:1.7; color:#6b5a49;">
        Questions before then? Reply here, call
        <a href="tel:{{ preg_replace('/\s+/', '', config('site.contact.phone')) }}"
           style="color:#3a2418;">{{ config('site.contact.phone') }}</a>,
        or message us on
        <a href="{{ $whatsapp }}" style="color:#3a2418;">WhatsApp</a>.
    </p>

    <p style="margin:18px 0 0; font-size:15px; line-height:1.7; color:#6b5a49;">
        We&rsquo;re looking forward to having you.
    </p>
</x-mail-layout>
