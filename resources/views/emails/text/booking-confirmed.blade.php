@php
    $trip = $inquiry->tour_name ?: 'your trip';
@endphp
Your booking is confirmed
=========================

Hi {{ $inquiry->name }},

Good news - we've confirmed your place on {{ $trip }}. Here is what we have on
file. If any of it looks wrong, just reply to this email and we'll put it right.

Your booking
------------
@if ($inquiry->tour_name)
Trip:       {{ $inquiry->tour_name }}
@endif
@if ($inquiry->travel_date)
Departure:  {{ $inquiry->travel_date->format('j F Y') }}
@endif
Travellers: {{ $inquiry->travellers }}
Reference:  TA-{{ str_pad((string) $inquiry->id, 5, '0', STR_PAD_LEFT) }}
@unless ($inquiry->travel_date)

We haven't fixed a departure date yet - we'll be in touch to settle that with you.
@endunless

What happens next
-----------------
One of our guides will contact you shortly with the full day-by-day plan, what
to pack, and how to settle the balance. Nothing is needed from you right now.
@if ($tour)

Your itinerary: {{ route('tours.show', $tour->slug) }}
@endif

Questions before then? Reply here, call {{ config('site.contact.phone') }},
or message us on WhatsApp: https://wa.me/{{ config('site.contact.whatsapp') }}

We're looking forward to having you.

--
{{ config('site.brand.name') }} {{ config('site.brand.suffix') }}
{{ config('site.contact.address') }}
{{ config('site.contact.email') }}
