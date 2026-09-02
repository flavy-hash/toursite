@props(['tour'])

<article class="group flex flex-col overflow-hidden rounded-3xl bg-light-sand shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">
    <a href="{{ route('tours.show', $tour['slug']) }}" class="relative block aspect-[4/3] overflow-hidden">
        <img
            src="{{ $tour->image_url }}"
            alt="{{ $tour['name'] }}"
            loading="lazy"
            class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
        >
        <span class="absolute left-4 top-4 rounded-full bg-dark-brown/80 px-3 py-1 text-[11px] uppercase tracking-[0.14em] text-cream backdrop-blur-md">
            {{ $tour['category'] }}
        </span>
        <span class="absolute right-4 top-4 rounded-full bg-light-sand/90 px-2.5 py-1 text-xs font-semibold text-dark-brown backdrop-blur-md">
            ★ {{ $tour['rating'] }}
        </span>
    </a>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="font-display text-xl leading-snug">
            <a href="{{ route('tours.show', $tour['slug']) }}" class="transition-colors hover:text-brown">
                {{ $tour['name'] }}
            </a>
        </h3>

        <p class="mt-2 text-sm font-light italic leading-relaxed text-brown/70">
            &ldquo;{{ $tour['highlight'] }}&rdquo;
        </p>

        <dl class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-xs text-brown/70">
            <div class="inline-flex items-center gap-1.5">
                <dt class="sr-only">Duration</dt>
                <x-ui-icon name="clock" class="h-4 w-4" />
                <dd>{{ $tour['days'] }}</dd>
            </div>
            <div class="inline-flex items-center gap-1.5">
                <dt class="sr-only">Group size</dt>
                <x-ui-icon name="users" class="h-4 w-4" />
                <dd>{{ $tour['group'] }}</dd>
            </div>
            <div class="inline-flex items-center gap-1.5">
                <dt class="sr-only">Difficulty</dt>
                <x-ui-icon name="gauge" class="h-4 w-4" />
                <dd>{{ $tour['difficulty'] }}</dd>
            </div>
        </dl>

        {{-- mt-auto pins the price row to the bottom so cards line up despite uneven titles. --}}
        <div class="mt-auto flex items-end justify-between gap-3 pt-5">
            <p>
                <span class="block text-[11px] uppercase tracking-[0.14em] text-brown/55">From</span>
                <span class="font-display text-2xl">{{ $tour['price'] }}</span>
            </p>

            <a href="{{ route('tours.show', $tour['slug']) }}" class="inline-flex items-center gap-1.5 rounded-full bg-brown px-4 py-2.5 text-xs uppercase tracking-[0.12em] text-cream transition-colors hover:bg-dark-brown">
                View
                <x-ui-icon name="arrow" class="h-3.5 w-3.5" />
            </a>
        </div>

        <p class="mt-2 text-[11px] text-brown/50">{{ $tour['reviews'] }} reviews</p>
    </div>
</article>
