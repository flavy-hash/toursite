<x-mail-layout :unsubscribe-url="$subscriber->unsubscribeUrl()" :title="$subjectLine">
    <p style="margin:0 0 16px; font-size:22px; color:#3a2418;">{{ $subjectLine }}</p>

    @if ($intro)
        {{-- Written in the admin panel; line breaks preserved, markup not. --}}
        <p style="margin:0 0 18px; font-size:15px; line-height:1.7; color:#6b5a49;">
            {!! nl2br(e($intro)) !!}
        </p>
    @endif

    @if ($tours->isNotEmpty())
        @include('emails.partials.tour-list', ['tours' => $tours])
    @endif

    <p style="margin:24px 0 0; font-size:15px; line-height:1.7; color:#6b5a49;">
        Reply to this email with any questions &mdash; we answer within a working day.
    </p>
</x-mail-layout>
