@php
    // Featured reviews, managed in the admin panel. The section is skipped
    // entirely when nothing is published, rather than showing an empty grid.
    $stories = \App\Models\Review::published()
        ->orderByDesc('is_featured')
        ->newestFirst()
        ->take(3)
        ->get();

    $summary = \App\Models\Review::summary();
@endphp

@if ($stories->isNotEmpty())
    <section class="bg-light-sand px-6 py-20 text-dark-brown sm:px-12 lg:px-20 lg:py-28">
        <div class="mx-auto max-w-7xl">

            <div class="flex flex-wrap items-end justify-between gap-6">
                <div class="max-w-xl">
                    <p class="text-xs uppercase tracking-[0.22em] text-brown/60">Traveller Stories</p>
                    <h2 class="mt-3 font-display text-4xl leading-tight lg:text-5xl">
                        Voices from the trail
                    </h2>

                    @if ($summary['average'])
                        <p class="mt-3 flex items-center gap-2 text-sm text-brown/70">
                            <x-stars :rating="round($summary['average'])" class="text-brown" />
                            <span class="font-semibold text-dark-brown">{{ number_format($summary['average'], 1) }}</span>
                            <span>from {{ $summary['total'] }} {{ Str::plural('review', $summary['total']) }}</span>
                        </p>
                    @endif
                </div>

                <a href="{{ route('reviews.index') }}" class="group inline-flex items-center gap-2 border-b border-brown/30 pb-1 text-sm font-medium transition-colors hover:border-brown">
                    @if ($summary['average'])
                        Read all {{ $summary['total'] }} {{ Str::plural('review', $summary['total']) }}
                    @else
                        Read all reviews
                    @endif
                    <x-ui-icon name="arrow" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                </a>
            </div>

            {{-- Same card as /reviews, so the two pages always match. --}}
            <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($stories as $story)
                    <x-review-card :review="$story" />
                @endforeach
            </div>
        </div>
    </section>
@endif
