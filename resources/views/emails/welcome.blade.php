<x-mail-layout :unsubscribe-url="$subscriber->unsubscribeUrl()" title="Welcome">
    <p style="margin:0 0 16px; font-size:22px; color:#3a2418;">You&rsquo;re on the list</p>

    <p style="margin:0 0 18px; font-size:15px; line-height:1.7; color:#6b5a49;">
        Thanks for subscribing. We send occasional notes on new routes, quiet season
        openings and where the herds are heading  nothing else.
    </p>

    @if ($tours->isNotEmpty())
        <p style="margin:26px 0 14px; font-size:11px; letter-spacing:2px; text-transform:uppercase; color:#a08f7c;">
            A few trips to start with
        </p>

        @include('emails.partials.tour-list', ['tours' => $tours])
    @endif

    <p style="margin:24px 0 0; font-size:15px; line-height:1.7; color:#6b5a49;">
        Planning something specific? Just reply to this email  a real person reads it.
    </p>
</x-mail-layout>
