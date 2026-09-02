@extends('layouts.app')

@section('title', 'Traveller Reviews')
@section('description', 'What our travellers say after their safari, Kilimanjaro climb or Zanzibar escape — in their own words.')

@if ($summary['total'] > 0)
    @push('schema')
        @php
            $ratingSchema = [
                '@context' => 'https://schema.org',
                '@type' => config('seo.organisation.type'),
                'name' => config('seo.organisation.name'),
                'url' => url('/'),
                'aggregateRating' => [
                    '@type' => 'AggregateRating',
                    'ratingValue' => $summary['average'],
                    'reviewCount' => $summary['total'],
                    'bestRating' => 5,
                ],
                'review' => $reviews->map(fn ($review) => array_filter([
                    '@type' => 'Review',
                    'author' => ['@type' => 'Person', 'name' => $review->name],
                    'datePublished' => $review->travelled_on?->toDateString(),
                    'name' => $review->title,
                    'reviewBody' => $review->body,
                    'reviewRating' => [
                        '@type' => 'Rating',
                        'ratingValue' => $review->rating,
                        'bestRating' => 5,
                    ],
                ]))->all(),
            ];
        @endphp
        <script type="application/ld+json">
        {!! json_encode($ratingSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endpush
@endif

@section('content')

    {{-- ── Header ────────────────────────────────────────────────────── --}}
    <section class="relative isolate flex min-h-[55svh] items-end overflow-hidden bg-dark-brown">
        <img
            src="{{ config('site.page_headers.reviews') }}"
            alt=""
            fetchpriority="high"
            class="absolute inset-0 -z-10 h-full w-full object-cover object-center"
        >
        <div class="page-wash absolute inset-0 -z-10"></div>

        <div class="relative w-full px-6 pb-16 pt-32 sm:px-12 lg:px-20">
            <div class="mx-auto max-w-7xl">
                <nav aria-label="Breadcrumb" class="mb-6 text-xs text-white/55">
                    <a href="/" class="transition-colors hover:text-white">Home</a>
                    <span class="mx-2">/</span>
                    <span class="text-white/85">Reviews</span>
                </nav>

                <p class="text-xs uppercase tracking-[0.22em] text-sand/70">Traveller Stories</p>
                <h1 class="mt-3 max-w-3xl font-display text-4xl leading-tight text-white sm:text-5xl lg:text-6xl">
                    What our travellers <em class="not-italic text-sand">are saying</em>
                </h1>

                <button type="button" data-open-review
                        class="mt-8 inline-flex items-center gap-2 rounded-full bg-sand px-7 py-4 text-xs uppercase tracking-[0.14em] text-dark-brown transition-colors hover:bg-cream">
                    Write a review
                    <x-ui-icon name="arrow" class="h-4 w-4" />
                </button>
            </div>
        </div>
    </section>

    {{-- ── Summary ───────────────────────────────────────────────────── --}}
    <section class="bg-cream px-6 py-14 text-dark-brown sm:px-12 lg:px-20">
        <div class="mx-auto max-w-7xl">
            @if ($summary['total'] > 0)
                <div class="grid gap-10 rounded-3xl border border-brown/12 bg-light-sand p-8 lg:grid-cols-12 lg:items-center lg:p-10">

                    <div class="text-center lg:col-span-3 lg:text-left">
                        <p class="font-display text-6xl leading-none">{{ number_format($summary['average'], 1) }}</p>
                        <x-stars :rating="round($summary['average'])" class="mt-3 text-brown" />
                        <p class="mt-3 text-sm text-brown/70">
                            Based on {{ $summary['total'] }}
                            {{ Str::plural('review', $summary['total']) }}
                        </p>
                    </div>

                    {{-- Distribution bars --}}
                    <div class="space-y-2 lg:col-span-5">
                        @foreach ($summary['distribution'] as $star => $count)
                            @php $share = $summary['total'] > 0 ? ($count / $summary['total']) * 100 : 0; @endphp
                            <div class="flex items-center gap-3 text-xs text-brown/70">
                                <span class="w-10 shrink-0">{{ $star }} star</span>
                                <span class="h-2 flex-1 overflow-hidden rounded-full bg-brown/10">
                                    <span class="block h-full rounded-full bg-brown/70" style="width: {{ $share }}%"></span>
                                </span>
                                <span class="w-6 shrink-0 text-right tabular-nums">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Category averages, shown only where travellers rated them --}}
                    <div class="grid grid-cols-2 gap-4 lg:col-span-4">
                        @foreach ([['Guiding', $summary['guiding']], ['Value for money', $summary['value']]] as [$label, $score])
                            @if ($score !== null)
                                <div class="rounded-2xl border border-brown/10 bg-cream p-5 text-center">
                                    <p class="font-display text-3xl">{{ number_format($score, 1) }}</p>
                                    <p class="mt-1 text-[11px] uppercase tracking-[0.14em] text-brown/55">{{ $label }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                <p class="text-center text-sm text-brown/70">
                    No reviews published yet — yours could be the first.
                </p>
            @endif
        </div>
    </section>

    {{-- ── Reviews ───────────────────────────────────────────────────── --}}
    <section class="bg-light-sand px-6 pb-20 pt-4 text-dark-brown sm:px-12 lg:px-20">
        <div class="mx-auto max-w-7xl">
            @if ($reviews->isNotEmpty())
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($reviews as $review)
                        <article class="flex h-full flex-col rounded-3xl border border-brown/10 bg-cream p-7">
                            <div class="flex items-center gap-4">
                                @if ($review->photo_url)
                                    <img src="{{ $review->photo_url }}" alt="" loading="lazy"
                                         class="h-12 w-12 shrink-0 rounded-full object-cover">
                                @else
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brown font-display text-lg text-cream">
                                        {{ $review->initials }}
                                    </span>
                                @endif

                                <div class="min-w-0">
                                    <p class="font-display text-lg leading-tight">{{ $review->name }}</p>
                                    @if ($review->location)
                                        <p class="text-xs text-brown/60">{{ $review->location }}</p>
                                    @endif
                                </div>
                            </div>

                            <x-stars :rating="$review->rating" class="mt-5 text-brown" />

                            @if ($review->title)
                                <h2 class="mt-4 font-display text-xl leading-snug">{{ $review->title }}</h2>
                            @endif

                            <blockquote class="mt-3 flex-1 text-[15px] font-light leading-relaxed text-brown/85">
                                {{ $review->body }}
                            </blockquote>

                            <footer class="mt-5 border-t border-brown/10 pt-4 text-xs text-brown/65">
                                @if ($review->tour_name)
                                    <p>{{ $review->tour_name }}</p>
                                @endif
                                @if ($review->travelled_on)
                                    <p class="mt-0.5 text-brown/50">Travelled {{ $review->travelled_on->format('F Y') }}</p>
                                @endif
                            </footer>
                        </article>
                    @endforeach
                </div>

                @if ($reviews->hasPages())
                    <div class="mt-12">{{ $reviews->links() }}</div>
                @endif
            @endif
        </div>
    </section>

    {{-- ── Write a review ────────────────────────────────────────────── --}}
    <section id="review-form" class="bg-cream px-6 py-20 text-dark-brown sm:px-12 lg:px-20">
        <div class="mx-auto max-w-2xl text-center">
            @if (session('review_submitted'))
                <div class="rounded-3xl border border-brown/15 bg-light-sand p-8" role="status">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brown text-cream">
                        <x-ui-icon name="quote" class="h-6 w-6" />
                    </span>
                    <h2 class="mt-5 font-display text-2xl">Thank you</h2>
                    <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-brown/75">
                        Your review has been sent for checking and will appear here shortly.
                    </p>
                </div>
            @else
                <p class="text-xs uppercase tracking-[0.22em] text-brown/60">Been with us?</p>
                <h2 class="mt-3 font-display text-3xl lg:text-4xl">Share your experience</h2>
                <p class="mx-auto mt-3 max-w-lg text-sm leading-relaxed text-brown/70">
                    We read every one. Reviews are checked before they appear, so yours may take a day or two to show up.
                </p>

                <button type="button" data-open-review
                        class="mt-8 inline-flex items-center gap-2 rounded-full bg-brown px-8 py-4 text-xs uppercase tracking-[0.14em] text-cream transition-colors hover:bg-dark-brown">
                    Write a review
                    <x-ui-icon name="arrow" class="h-4 w-4" />
                </button>
            @endif
        </div>
    </section>

    @include('partials.review-modal')

@endsection
