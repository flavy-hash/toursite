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
            <p style="margin:0; font-size:22px; letter-spacing:1px; color:#f0e6d2;">
                {{ config('site.brand.name') }}
            </p>
            <p style="margin:6px 0 0; font-size:10px; letter-spacing:4px; text-transform:uppercase; color:#e5d3b3;">
                {{ config('site.brand.suffix') }}
            </p>
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
            {{-- Required on marketing email, and it must be one click. --}}
            <p style="margin:0;">
                <a href="{{ $unsubscribeUrl }}" style="color:#8a7a68; text-decoration:underline;">
                    Unsubscribe from these emails
                </a>
            </p>
        </div>
    </div>
</body>
</html>
