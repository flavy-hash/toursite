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

<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/favicon-32x32.png" sizes="32x32" type="image/png">
<link rel="icon" href="/favicon-16x16.png" sizes="16x16" type="image/png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

{{-- Organisation record, on every page so any entry point can be crawled. --}}
@php
    $organisation = array_filter([
        '@context' => 'https://schema.org',
        '@type' => config('seo.organisation.type'),
        'name' => config('seo.organisation.name'),
        'url' => url('/'),
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
        'sameAs' => array_values(array_filter(config('seo.social', []))),
    ]);
@endphp

<script type="application/ld+json">
{!! json_encode($organisation, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

{{-- Page-specific structured data, pushed by individual views. --}}
@stack('schema')
