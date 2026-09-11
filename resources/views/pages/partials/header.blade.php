{{--
    Shared header for the editable pages. Matches the tours and reviews
    headers so /about does not look like a different site.

    $page       the Page record
    $crumb      breadcrumb trail: [label => href|null], last entry is current
--}}
<section class="relative isolate flex min-h-[55svh] items-end overflow-hidden bg-dark-brown">
    @if ($page->hero_image_url)
        <img
            src="{{ $page->hero_image_url }}"
            alt=""
            fetchpriority="high"
            class="absolute inset-0 -z-10 h-full w-full object-cover object-center"
        >
    @endif

    <div class="page-wash absolute inset-0 -z-10"></div>

    <div class="relative w-full px-6 pb-16 pt-32 sm:px-12 lg:px-20">
        <div class="mx-auto max-w-7xl">
            <nav aria-label="Breadcrumb" class="mb-6 text-xs text-white/55">
                <a href="/" class="transition-colors hover:text-white">Home</a>

                @foreach ($crumb as $label => $href)
                    <span class="mx-2">/</span>

                    @if ($href)
                        <a href="{{ $href }}" class="transition-colors hover:text-white">{{ $label }}</a>
                    @else
                        <span class="text-white/85">{{ $label }}</span>
                    @endif
                @endforeach
            </nav>

            @if ($page->eyebrow)
                <p class="text-xs uppercase tracking-[0.22em] text-sand/70">{{ $page->eyebrow }}</p>
            @endif

            <h1 class="mt-3 max-w-3xl font-display text-4xl leading-tight text-white sm:text-5xl lg:text-6xl">
                {{ $page->heading ?: $page->title }}
            </h1>

            @if ($page->intro)
                <p class="mt-6 max-w-2xl text-base leading-relaxed text-white/70">
                    {{ $page->intro }}
                </p>
            @endif
        </div>
    </div>
</section>
