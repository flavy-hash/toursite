@extends('layouts.app')

@section('title', 'All Tanzania Tours & Safari Packages')
@section('description', 'Every safari, Kilimanjaro route and Zanzibar escape we run, with prices, durations and group sizes.')

@push('schema')
    @php
        $itemList = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'Tanzania tour packages',
            'numberOfItems' => count($tours),
            'itemListElement' => collect($tours)->values()->map(fn ($tour, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'url' => route('tours.show', $tour->slug),
                'name' => $tour->name,
            ])->all(),
        ];
    @endphp
    <script type="application/ld+json">
    {!! json_encode($itemList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')

    {{-- Same proportions as a package hero, so the two pages sit level. --}}
    <section class="relative isolate flex min-h-[70svh] items-end overflow-hidden bg-dark-brown">
        <img
            src="{{ config('site.page_headers.tours') }}"
            alt=""
            fetchpriority="high"
            class="absolute inset-0 -z-10 h-full w-full object-cover object-center"
        >
        {{-- Even scrim, so the type stays readable over any part of the photo. --}}
        <div class="page-wash absolute inset-0 -z-10"></div>

        {{-- Padding on the full-width wrapper, cap on the inner box — matches
             the grid below, so the heading and the cards share a left edge. --}}
        <div class="relative w-full px-6 pb-16 pt-32 sm:px-12 lg:px-20">
            <div class="mx-auto max-w-7xl">
                <nav aria-label="Breadcrumb" class="mb-6 text-xs text-white/55">
                    <a href="/" class="transition-colors hover:text-white">Home</a>
                    <span class="mx-2">/</span>
                    <span class="text-white/85">Tours</span>
                </nav>

                <p class="text-xs uppercase tracking-[0.22em] text-sand/70">Tours &amp; Experiences</p>
                <h1 class="mt-3 max-w-2xl font-display text-4xl leading-tight text-white sm:text-5xl lg:text-6xl">
                    Every trip we run
                </h1>
                <p class="mt-4 max-w-xl text-base font-light leading-relaxed text-white/70">
                    Safaris, Kilimanjaro routes and island escapes — all privately guided, all built around small groups.
                </p>
            </div>
        </div>
    </section>

    <section class="bg-light-sand px-6 py-12 text-dark-brown sm:px-12 lg:px-20 lg:py-16">
        <div class="mx-auto max-w-7xl">

            {{-- Category filter. Plain links, so each view is shareable and
                 works with the back button. --}}
            <div class="flex flex-wrap items-center gap-2.5">
                <a
                    href="{{ route('tours.index') }}"
                    @class(['filter-chip', 'is-active' => ! isset($active['category'])])
                >
                    All Tours
                </a>

                @foreach ($categories as $category)
                    <a
                        href="{{ route('tours.index', ['category' => strtolower($category)]) }}"
                        @class([
                            'filter-chip',
                            'is-active' => isset($active['category'])
                                && strcasecmp($active['category'], $category) === 0,
                        ])
                    >
                        {{ $category }}
                    </a>
                @endforeach
            </div>

            {{-- Any filter the chips don't cover — region, tier — is still
                 honoured, so nav links land somewhere truthful. --}}
            @php $extra = collect($active)->except('category'); @endphp

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                <p class="text-sm text-brown/70">
                    Showing <strong>{{ count($tours) }}</strong> of {{ $total }}
                    {{ Str::plural('tour', $total) }}

                    @if ($extra->isNotEmpty())
                        <span class="text-brown/55">
                            —
                            @foreach ($extra as $key => $value)
                                {{ $key }}: <em>{{ $value }}</em>{{ ! $loop->last ? ',' : '' }}
                            @endforeach
                        </span>
                    @endif
                </p>

                @if ($active)
                    <a href="{{ route('tours.index') }}" class="inline-flex items-center gap-2 text-sm text-brown/70 underline-offset-4 transition-colors hover:text-brown hover:underline">
                        Clear filters
                    </a>
                @endif
            </div>

            @if ($tours->isNotEmpty())
                <div class="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($tours as $tour)
                        <x-tour-card :tour="$tour" />
                    @endforeach
                </div>
            @else
                {{-- Southern Circuit and the tier filters have no packages yet,
                     so this is a route people will actually hit. --}}
                <div class="mt-10 rounded-3xl border border-brown/12 bg-cream p-10 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brown/10 text-brown">
                        <x-ui-icon name="compass" class="h-6 w-6" />
                    </span>

                    <h2 class="mt-5 font-display text-2xl">Nothing here yet</h2>
                    <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-brown/70">
                        We have no packages matching that filter at the moment. Tell us what you are after and we will
                        put an itinerary together, or browse everything we currently run.
                    </p>

                    <div class="mt-7 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('tours.index') }}" class="rounded-full bg-brown px-6 py-3 text-xs uppercase tracking-[0.14em] text-cream transition-colors hover:bg-dark-brown">
                            See All Tours
                        </a>
                        <a href="{{ route('inquiry.create') }}" class="rounded-full border border-brown/30 px-6 py-3 text-xs uppercase tracking-[0.14em] transition-colors hover:bg-brown/10">
                            Request a Custom Trip
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </section>

@endsection
