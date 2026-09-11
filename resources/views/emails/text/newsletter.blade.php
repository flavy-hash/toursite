{{ $subjectLine }}
{{ str_repeat('=', min(strlen($subjectLine), 70)) }}
@if ($intro)

{{ $intro }}
@endif
@foreach ($tours as $tour)

{{ $tour->name }}
{{ $tour->days }}, from {{ $tour->price }}
{{ route('tours.show', $tour->slug) }}
@endforeach

Questions? Just reply to this email - a real person reads it.

--
{{ config('site.brand.name') }} {{ config('site.brand.suffix') }}
{{ config('site.contact.address') }}
{{ config('site.contact.phone') }}
{{ url('/') }}

Unsubscribe: {{ $subscriber->unsubscribeUrl() }}
