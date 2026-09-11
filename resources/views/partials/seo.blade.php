@php
    use Illuminate\Support\Str;

    /*
     * Laravel runs e() over inline @section content, so anything yielded here
     * arrives already escaped. Decode it first and let {{ }} escape once —
     * otherwise an ampersand in a title renders as "&amp;amp;".
     */
    $yield = fn (string $section) => html_entity_decode(
        trim($__env->yieldContent($section)),
        ENT_QUOTES | ENT_HTML5,
    );

    $pageTitle = $yield('title');
    $title = $pageTitle !== ''
        ? $pageTitle . config('seo.title_suffix')
        : config('seo.default_title') . config('seo.title_suffix');

    // Search engines truncate around 160 characters.
    $description = Str::limit($yield('description') ?: config('seo.default_description'), 160);

    // Social scrapers do not resolve relative paths, so the share image and
    // canonical must both be absolute.
    $image = url($yield('og_image') ?: config('seo.default_image'));
    $canonical = url()->current();
    $type = $yield('og_type') ?: 'website';
    $robots = $yield('robots') ?: 'index, follow';
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">

<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="{{ config('seo.organisation.name') }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:locale" content="{{ config('seo.locale') }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $title }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">
@if (config('seo.twitter_handle'))
    <meta name="twitter:site" content="{{ config('seo.twitter_handle') }}">
@endif

<meta name="theme-color" content="#3a2418">

{{--
    Browser-tab icons, generated from the logo mark by
    scripts/build-favicons.php. Re-run it whenever the artwork changes.

    favicon.ico is listed first and last: old browsers request /favicon.ico
    regardless, and it was an empty file, which is why the tab was showing a
    generated letter instead of the logo.
--}}
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon-32x32.png" sizes="32x32" type="image/png">
<link rel="icon" href="/favicon-16x16.png" sizes="16x16" type="image/png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">

{{--
    Site-wide structured data, as one connected @graph.

    Organisation and WebSite are cross-referenced by @id rather than emitted as
    two unrelated blocks, so a crawler reads them as one business with one site
    rather than guessing they belong together. That linkage is what lets Google
    settle on a site name and logo for the result header — and a site it
    understands the shape of is one it is more willing to show sitelinks under.

    Sitelinks themselves cannot be requested or marked up: Google chooses them
    from the site's structure, its internal links and each page's own title and
    description. Everything here exists to make that choice easy.
--}}
@php
    use App\Models\Review;

    $organisationId = url('/') . '#organisation';
    $websiteId = url('/') . '#website';

    /*
     * Ratings are read from the same published reviews the reviews page shows,
     * so the two can never disagree. Omitted entirely when there are none —
     * an aggregateRating with a zero count is a structured-data error, not a
     * neutral statement.
     */
    $ratings = Review::summary();

    $aggregateRating = ($ratings['total'] ?? 0) > 0 && ($ratings['average'] ?? null)
        ? [
            '@type' => 'AggregateRating',
            'ratingValue' => $ratings['average'],
            'reviewCount' => $ratings['total'],
            'bestRating' => 5,
            'worstRating' => 1,
        ]
        : null;

    $organisation = array_filter([
        '@type' => config('seo.organisation.type'),
        '@id' => $organisationId,
        'name' => config('seo.organisation.name'),
        'url' => url('/'),
        // Google wants a square logo of at least 112px for the result header.
        'logo' => [
            '@type' => 'ImageObject',
            'url' => url('/favicon-512x512.png'),
            'width' => 512,
            'height' => 512,
        ],
        'image' => $image,
        'description' => config('seo.default_description'),
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => config('seo.organisation.locality'),
            'addressRegion' => config('seo.organisation.region'),
            'addressCountry' => config('seo.organisation.country'),
        ],
        'email' => config('site.contact.email'),
        'telephone' => config('site.contact.phone'),
        'areaServed' => config('seo.organisation.area_served'),
        'knowsAbout' => config('seo.organisation.knows_about'),
        'aggregateRating' => $aggregateRating,
        // sameAs is how search engines tie these profiles to the business.
        'sameAs' => array_values(array_filter(array_merge(
            config('site.social', []),
            config('seo.social', []),
        ))),
    ]);

    $website = array_filter([
        '@type' => 'WebSite',
        '@id' => $websiteId,
        'name' => config('seo.organisation.name'),
        // The bare domain, which is what Google often prints above a result.
        'alternateName' => parse_url(url('/'), PHP_URL_HOST),
        'url' => url('/'),
        'description' => config('seo.default_description'),
        'inLanguage' => str_replace('_', '-', config('seo.locale')),
        'publisher' => ['@id' => $organisationId],
    ]);

    $graph = [
        '@context' => 'https://schema.org',
        '@graph' => [$organisation, $website],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

{{-- Page-specific structured data, pushed by individual views. --}}
@stack('schema')
