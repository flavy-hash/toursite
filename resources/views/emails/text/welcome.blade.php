{{--
    Plain-text alternative. Every message should carry one: an HTML-only email
    is a long-standing spam signal, and some clients show nothing else.
--}}
You're on the list
==================

Thanks for subscribing to {{ config('site.brand.name') }} {{ config('site.brand.suffix') }}.

We send occasional notes on new routes, quiet season openings and where the
herds are heading - nothing else.
@if ($tours->isNotEmpty())

A few trips to start with
-------------------------
@foreach ($tours as $tour)

{{ $tour->name }} - {{ $tour->days }}, from {{ $tour->price }}
{{ route('tours.show', $tour->slug) }}
@endforeach
@endif

Planning something specific? Just reply to this email - a real person reads it.

--
{{ config('site.brand.name') }} {{ config('site.brand.suffix') }}
{{ config('site.contact.address') }}
{{ config('site.contact.phone') }}
{{ url('/') }}

Unsubscribe: {{ $subscriber->unsubscribeUrl() }}
