@extends('layouts.app')

@section('title', $tour->name)
{{-- Must never be null: Blade reads @section('x', null) as the start of a
     buffered block and waits for an @endsection that never comes. --}}
@section('description', $tour->tagline ?: config('site.brand.tagline'))
@section('og_type', 'product')
@section('og_image', $tour->image_url ?: config('seo.default_image'))

@push('schema')
    @php
        $trip = [
            '@context' => 'https://schema.org',
            '@type' => 'TouristTrip',
            'name' => $tour->name,
            'description' => $tour->tagline ?: implode(' ', $tour->summary ?? []),
            'url' => route('tours.show', $tour->slug),
            'image' => url($tour->image_url ?? config('seo.default_image')),
            'touristType' => $tour->category,
            'itinerary' => [
                '@type' => 'ItemList',
                'numberOfItems' => count($tour->itinerary ?? []),
                'itemListElement' => collect($tour->itinerary ?? [])->values()->map(fn ($day, $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'item' => [
                        '@type' => 'TouristAttraction',
                        'name' => $day['title'] ?? ('Day ' . ($i + 1)),
                        'description' => $day['copy'] ?? null,
                    ],
                ])->all(),
            ],
            'provider' => [
                '@type' => config('seo.organisation.type'),
                'name' => config('seo.organisation.name'),
                'url' => url('/'),
            ],
            'offers' => [
                '@type' => 'Offer',
                // Stored as display text such as "$2,450"; strip it back to a
                // number so the offer is machine-readable.
                'price' => (float) preg_replace('/[^0-9.]/', '', (string) $tour->price),
                'priceCurrency' => 'USD',
                'availability' => 'https://schema.org/InStock',
                'url' => route('tours.show', $tour->slug),
            ],
            // Claiming a rating with no reviews behind it is a structured-data
            // violation, so it is omitted entirely rather than sent as zero.
            'aggregateRating' => $tour->reviews > 0 ? [
                '@type' => 'AggregateRating',
                'ratingValue' => (float) $tour->rating,
                'reviewCount' => (int) $tour->reviews,
                'bestRating' => 5,
            ] : null,
        ];

        $breadcrumbs = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Tours', 'item' => route('tours.index')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $tour->name, 'item' => route('tours.show', $tour->slug)],
            ],
        ];
    @endphp

    <script type="application/ld+json">
    {!! json_encode($trip, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <script type="application/ld+json">
    {!! json_encode($breadcrumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')

    {{-- ── Hero ──────────────────────────────────────────────────────── --}}
    <section class="relative isolate flex min-h-[70svh] items-end overflow-hidden">
        <img src="{{ $tour->image_url }}" alt="" class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
        <div class="hero-wash absolute inset-0"></div>

        <div class="relative w-full px-6 pb-16 pt-32 sm:px-12 lg:px-20">
            <div class="mx-auto max-w-7xl">
                <nav aria-label="Breadcrumb" class="mb-6 text-xs text-white/60">
                    <a href="/" class="transition-colors hover:text-white">Home</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('tours.index') }}" class="transition-colors hover:text-white">Tours</a>
                    <span class="mx-2">/</span>
                    <span class="text-white/85">{{ $tour['name'] }}</span>
                </nav>

                <p class="inline-flex items-center gap-2 rounded-full border border-sand/25 bg-sand/10 px-4 py-1.5 text-[11px] uppercase tracking-[0.16em] text-white backdrop-blur-md">
                    {{ $tour['category'] }}
                </p>

                <h1 class="mt-5 max-w-3xl font-display text-4xl leading-[1.08] text-white sm:text-5xl lg:text-6xl">
                    {{ $tour['name'] }}
                </h1>

                <p class="mt-4 max-w-xl text-lg font-light text-white/80">{{ $tour['tagline'] }}</p>

                <div class="mt-7 flex flex-wrap items-center gap-x-8 gap-y-3 text-white">
                    <span class="flex items-center gap-2">
                        <x-stars :rating="round($tour['rating'])" class="text-sand" />
                        <span class="font-semibold">{{ $tour['rating'] }}</span>
                        <span class="text-sm text-white/60">· {{ $tour['reviews'] }} reviews</span>
                    </span>

                    <span class="inline-flex items-center gap-2 text-sm text-white/75">
                        <x-ui-icon name="pin" class="h-4 w-4" />
                        {{ $tour['location'] }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Quick facts ───────────────────────────────────────────────── --}}
    <section class="bg-cream px-6 py-8 text-dark-brown sm:px-12 lg:px-20">
        <dl class="mx-auto grid max-w-7xl grid-cols-2 gap-6 md:grid-cols-5">
            @foreach ([
                ['Duration', $tour['days'], 'clock'],
                ['Group size', $tour['group'], 'users'],
                ['Difficulty', $tour['difficulty'], 'gauge'],
                ['Best time', $tour['best_time'], 'calendar'],
                ['Starts / ends', $tour['start'] . ' · ' . $tour['end'], 'pin'],
            ] as [$label, $value, $icon])
                <div class="flex items-start gap-3">
                    <x-ui-icon :name="$icon" class="mt-0.5 h-5 w-5 shrink-0 text-brown/50" />
                    <div>
                        <dt class="text-[11px] uppercase tracking-[0.14em] text-brown/55">{{ $label }}</dt>
                        <dd class="mt-0.5 text-sm font-medium">{{ $value }}</dd>
                    </div>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- ── Body ──────────────────────────────────────────────────────── --}}
    <section class="bg-light-sand px-6 py-20 text-dark-brown sm:px-12 lg:px-20 lg:py-24">
        <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-12 lg:items-start">

            <div class="lg:col-span-8">

                <h2 class="font-display text-3xl lg:text-4xl">Overview</h2>
                @foreach ($tour['summary'] ?? [] as $paragraph)
                    <p class="mt-4 max-w-2xl text-[15px] font-light leading-relaxed text-brown/80">{{ $paragraph }}</p>
                @endforeach

                {{-- Highlights --}}
                <h2 class="mt-14 font-display text-3xl lg:text-4xl">Highlights</h2>
                <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                    @foreach ($tour['highlights'] ?? [] as $highlight)
                        <li class="flex gap-3 rounded-2xl border border-brown/10 bg-cream p-4">
                            <x-ui-icon name="compass" class="mt-0.5 h-5 w-5 shrink-0 text-brown/45" />
                            <span class="text-sm leading-relaxed text-brown/85">{{ $highlight }}</span>
                        </li>
                    @endforeach
                </ul>

                {{-- Itinerary. <details> gives an accordion with no JavaScript,
                     and day one opens by default. --}}
                <h2 class="mt-14 font-display text-3xl lg:text-4xl">Itinerary</h2>
                <div class="mt-6 space-y-3">
                    @foreach ($tour['itinerary'] ?? [] as $i => $day)
                        <details
                            class="group overflow-hidden rounded-2xl border border-brown/12 bg-cream"
                            @if ($i === 0) open @endif
                        >
                            <summary class="flex cursor-pointer list-none items-center gap-4 p-5">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brown font-display text-lg text-cream">
                                    {{ $day['day'] ?? $i + 1 }}
                                </span>

                                <span class="flex-1 font-display text-lg leading-snug">{{ $day['title'] ?? 'Day ' . ($i + 1) }}</span>

                                <x-ui-icon name="chevron" class="h-4 w-4 shrink-0 text-brown/50 transition-transform group-open:rotate-180" />
                            </summary>

                            <div class="border-t border-brown/10 px-5 pb-5 pt-4">
                                <p class="text-sm font-light leading-relaxed text-brown/80">{{ $day['copy'] ?? '' }}</p>

                                {{-- Both are optional in the admin form, so each
                                     is skipped rather than printed empty. --}}
                                @if (filled($day['stay'] ?? null) || filled($day['meals'] ?? null))
                                    <div class="mt-4 flex flex-wrap gap-x-8 gap-y-2 text-xs text-brown/60">
                                        @if (filled($day['stay'] ?? null))
                                            <span><span class="uppercase tracking-[0.12em] text-brown/45">Stay</span> · {{ $day['stay'] }}</span>
                                        @endif
                                        @if (filled($day['meals'] ?? null))
                                            <span><span class="uppercase tracking-[0.12em] text-brown/45">Meals</span> · {{ $day['meals'] }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </details>
                    @endforeach
                </div>

                {{-- Inclusions --}}
                <h2 class="mt-14 font-display text-3xl lg:text-4xl">What&rsquo;s included</h2>
                <div class="mt-6 grid gap-6 sm:grid-cols-2">
                    <div class="rounded-2xl border border-brown/10 bg-cream p-6">
                        <p class="text-[11px] uppercase tracking-[0.16em] text-brown/55">Included</p>
                        <ul class="mt-4 space-y-2.5">
                            @foreach ($tour['included'] ?? [] as $line)
                                <li class="flex gap-2.5 text-sm text-brown/85">
                                    <span aria-hidden="true" class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-brown/60"></span>
                                    {{ $line }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-brown/10 p-6">
                        <p class="text-[11px] uppercase tracking-[0.16em] text-brown/55">Not included</p>
                        <ul class="mt-4 space-y-2.5">
                            @foreach ($tour['excluded'] ?? [] as $line)
                                <li class="flex gap-2.5 text-sm text-brown/65">
                                    <span aria-hidden="true" class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-brown/25"></span>
                                    {{ $line }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Gallery. Skipped entirely when the package has no photos,
                     rather than leaving a heading over an empty grid. --}}
                @if ($tour->gallery_urls)
                    <h2 class="mt-14 font-display text-3xl lg:text-4xl">Gallery</h2>
                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        @foreach ($tour->gallery_urls as $image)
                            <img
                                src="{{ $image }}"
                                alt="{{ $tour['name'] }}"
                                loading="lazy"
                                class="aspect-[4/3] w-full rounded-2xl object-cover"
                            >
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Booking card. Sticks alongside the itinerary on desktop. --}}
            <aside class="lg:col-span-4 lg:sticky lg:top-28">
                <div class="rounded-3xl border border-brown/12 bg-dark-brown p-7 text-cream">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-sand/70">From</p>
                    <p class="mt-1 font-display text-4xl text-white">{{ $tour['price'] }}</p>
                    <p class="mt-2 text-xs leading-relaxed text-white/55">{{ $tour['price_note'] }}</p>

                    <dl class="mt-6 space-y-3 border-y border-sand/15 py-5 text-sm">
                        @foreach ([
                            'Duration' => $tour['days'] . ' · ' . $tour['nights'],
                            'Group size' => $tour['group'],
                            'Difficulty' => $tour['difficulty'],
                            'Best time' => $tour['best_time'],
                        ] as $label => $value)
                            <div class="flex justify-between gap-4">
                                <dt class="text-white/55">{{ $label }}</dt>
                                <dd class="text-right font-medium">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    <a href="/inquiry?tour={{ $tour['slug'] }}" class="mt-6 flex w-full items-center justify-center gap-2 rounded-full bg-sand px-6 py-4 text-xs uppercase tracking-[0.14em] text-dark-brown transition-colors hover:bg-cream">
                        Book This Adventure
                        <x-ui-icon name="arrow" class="h-4 w-4" />
                    </a>

                    <a href="/contact" class="mt-3 flex w-full items-center justify-center gap-2 rounded-full border border-sand/35 px-6 py-4 text-xs uppercase tracking-[0.14em] text-cream transition-colors hover:bg-sand/15">
                        Ask a Question
                    </a>

                    <p class="mt-5 text-center text-xs text-white/45">
                        No payment taken online — we confirm availability first.
                    </p>
                </div>
            </aside>
        </div>
    </section>

    {{-- ── Related ───────────────────────────────────────────────────── --}}
    @if ($related->isNotEmpty())
        <section class="bg-cream px-6 py-20 text-dark-brown sm:px-12 lg:px-20">
            <div class="mx-auto max-w-7xl">
                <h2 class="font-display text-3xl lg:text-4xl">You might also like</h2>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $other)
                        <x-tour-card :tour="$other" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection
