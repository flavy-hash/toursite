@props(['review', 'heading' => 'h3'])

{{--
    One review card, shared by the reviews page and the homepage so the two
    cannot drift apart.

    The headline element is configurable because heading level depends on the
    page: on /reviews the cards sit under an <h1>, on the homepage they sit
    under a section <h2>.
--}}
<article {{ $attributes->merge(['class' => 'flex h-full flex-col rounded-3xl border border-brown/10 bg-cream p-7']) }}>
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
        <{{ $heading }} class="mt-4 font-display text-xl leading-snug">{{ $review->title }}</{{ $heading }}>
    @endif

    <blockquote class="mt-3 flex-1 text-[15px] font-light leading-relaxed text-brown/85">
        {{ $review->body }}
    </blockquote>

    @if ($review->tour_name || $review->travelled_on)
        <footer class="mt-5 border-t border-brown/10 pt-4 text-xs text-brown/65">
            @if ($review->tour_name)
                <p>{{ $review->tour_name }}</p>
            @endif
            @if ($review->travelled_on)
                <p class="mt-0.5 text-brown/50">Travelled {{ $review->travelled_on->format('F Y') }}</p>
            @endif
        </footer>
    @endif
</article>
