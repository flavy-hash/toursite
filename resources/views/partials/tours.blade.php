@php
    // Admin-managed. Featured packages lead; the section is skipped entirely
    // if nothing is published, rather than rendering an empty grid.
    $tours = \App\Models\Tour::published()
        ->orderByDesc('is_featured')
        ->ordered()
        ->take(4)
        ->get();
@endphp

@if ($tours->isNotEmpty())

<section class="bg-cream px-6 py-20 text-dark-brown sm:px-12 lg:px-20 lg:py-28">
    <div class="mx-auto max-w-7xl">

        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.22em] text-brown/60">Featured Adventures</p>
                <h2 class="mt-3 max-w-xl font-display text-4xl leading-tight lg:text-5xl">
                    Trips worth clearing your calendar for
                </h2>
            </div>

            <a href="{{ route('tours.index') }}" class="group inline-flex items-center gap-2 border-b border-brown/30 pb-1 text-sm font-medium transition-colors hover:border-brown">
                Explore All Tours
                <x-ui-icon name="arrow" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
            </a>
        </div>

        <div class="mt-12 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($tours as $tour)
                <x-tour-card :tour="$tour" />
            @endforeach
        </div>
    </div>
</section>
@endif
