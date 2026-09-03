@props(['unsubscribeUrl', 'title' => null])

@include('emails.layout', [
    'slot' => $slot,
    'unsubscribeUrl' => $unsubscribeUrl,
    'title' => $title,
])
