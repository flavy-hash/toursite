{{--
    unsubscribeUrl is optional: transactional mail (a booking confirmation)
    leaves it out, and the layout then shows contact details instead.
--}}
@props(['unsubscribeUrl' => null, 'title' => null])

@include('emails.layout', [
    'slot' => $slot,
    'unsubscribeUrl' => $unsubscribeUrl,
    'title' => $title,
])
