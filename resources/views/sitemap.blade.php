<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach ($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
        @isset($url['lastmod'])<lastmod>{{ $url['lastmod'] }}</lastmod>@endisset
        <changefreq>{{ $url['freq'] }}</changefreq>
        <priority>{{ $url['priority'] }}</priority>
        @if (!empty($url['image']))
        <image:image>
            <image:loc>{{ $url['image'] }}</image:loc>
            <image:caption>{{ $url['caption'] }}</image:caption>
        </image:image>
        @endif
    </url>
@endforeach
</urlset>
