{{--
    Plain-HTML email layout. Deliberately table-free but style-inline heavy:
    email clients strip <style> blocks unpredictably, so anything that matters
    is set on the element.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('site.brand.name') }}</title>
</head>
<body style="margin:0; padding:0; background:#f5f0e6; font-family:Helvetica,Arial,sans-serif; color:#3a2418;">
    <div style="max-width:600px; margin:0 auto; padding:24px 16px;">

        <div style="padding:28px 24px; background:#3a2418; border-radius:18px 18px 0 0; text-align:center;">
            {{--
                Absolute URL: an email has no page to resolve a relative path
                against. The logo is a cream wordmark on transparency, which is
                why it sits on the brown block rather than the white body.

                width/height are set as attributes as well as in the style,
                because Outlook ignores CSS dimensions on images. The file is
                896px wide and shown at 240, so it stays sharp on retina.

                alt carries the brand name so a client that blocks remote
                images — Outlook does by default — still shows who sent this.
            --}}
            <img src="{{ url('/assets/images/logo-side.png') }}"
                 alt="{{ config('site.brand.name') }} {{ config('site.brand.suffix') }}"
                 width="240" height="44"
                 style="display:block; margin:0 auto; width:240px; height:auto; max-width:100%;
                        border:0; outline:none; text-decoration:none;
                        font-size:20px; letter-spacing:1px; color:#f0e6d2;">
        </div>

        <div style="padding:32px 24px; background:#ffffff; border-radius:0 0 18px 18px;">
            {{ $slot }}
        </div>

        <div style="padding:22px 12px; text-align:center; font-size:12px; line-height:1.7; color:#8a7a68;">
            <p style="margin:0 0 6px;">
                {{ config('site.brand.name') }} {{ config('site.brand.suffix') }} &middot;
                {{ config('site.contact.address') }}
            </p>
            <p style="margin:0 0 10px;">
                <a href="{{ url('/') }}" style="color:#8a7a68;">{{ parse_url(url('/'), PHP_URL_HOST) }}</a>
            </p>
            {{--
                Required on marketing email, and it must be one click. Absent on
                transactional mail such as a booking confirmation, which the
                recipient must receive whether or not they take the newsletter —
                offering to unsubscribe from it would be misleading.
            --}}
            @if (! empty($unsubscribeUrl))
                <p style="margin:0;">
                    <a href="{{ $unsubscribeUrl }}" style="color:#8a7a68; text-decoration:underline;">
                        Unsubscribe from these emails
                    </a>
                </p>
            @else
                <p style="margin:0;">
                    {{ config('site.contact.email') }} &middot; {{ config('site.contact.phone') }}
                </p>
            @endif
        </div>
    </div>
</body>
</html>
