@props(['trail' => []])

@php
    /*
     * BreadcrumbList structured data.
     *
     * Google uses this to show the page's position as a path under the result
     * instead of a bare URL, and a site whose hierarchy it can read is a site
     * it is more willing to show sitelinks for.
     *
     * Home is prepended here so no caller has to remember it, and positions are
     * numbered from the final list — a gap in the sequence invalidates the lot.
     */
    $items = collect([['name' => 'Home', 'url' => url('/')]])
        ->concat($trail)
        ->filter(fn (array $item) => filled($item['name'] ?? null) && filled($item['url'] ?? null))
        ->values()
        ->map(fn (array $item, int $index) => [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $item['name'],
            'item' => $item['url'],
        ])
        ->all();

    $breadcrumbs = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $items,
    ];
@endphp

@if (count($items) > 1)
    <script type="application/ld+json">
    {!! json_encode($breadcrumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endif
