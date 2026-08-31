@php
    $slides = config('site.hero.slides');
    $stats = config('site.hero.stats');
    $reviews = config('site.hero.reviews');

    // The crater leads the carousel; the rest queue up as thumbnails beside it.
    $initial = 2;
@endphp

<section
    data-hero
    data-hero-initial="{{ $initial }}"
    class="relative isolate min-h-[100svh] overflow-hidden"
    aria-roledescription="carousel"
    aria-label="Featured destinations"
>
    {{-- Slide artwork --}}
    <div class="absolute inset-0" aria-hidden="true">
        @foreach ($slides as $i => $slide)
            <div class="hero-slide" data-hero-slide="{{ $i }}" data-active="{{ $i === $initial ? 'true' : 'false' }}">
                <img
                    src="{{ $slide['image'] }}"
                    alt=""
                    class="h-full w-full object-cover"
                    @if ($i === $initial) fetchpriority="high" @else loading="lazy" @endif
                >
            </div>
        @endforeach
        <div class="hero-wash absolute inset-0"></div>
    </div>

    {{-- Previous / next --}}
    <button
        type="button"
        data-hero-prev
        class="absolute left-3 top-1/2 z-20 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-sand/25 bg-sand/10 text-white backdrop-blur-md transition hover:bg-sand/20 sm:left-6 sm:h-12 sm:w-12"
    >
        <span class="sr-only">Previous destination</span>
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="m15 18-6-6 6-6"/>
        </svg>
    </button>

    <button
        type="button"
        data-hero-next
        class="absolute right-3 top-1/2 z-20 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-sand/25 bg-sand/10 text-white backdrop-blur-md transition hover:bg-sand/20 sm:right-6 sm:h-12 sm:w-12"
    >
        <span class="sr-only">Next destination</span>
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="m9 18 6-6-6-6"/>
        </svg>
    </button>

    {{-- Slide copy + thumbnails --}}
    <div class="relative z-10 flex min-h-[100svh] flex-col justify-center px-6 pb-44 pt-28 sm:px-12 lg:px-20 lg:pb-40">
        <div class="grid w-full items-center gap-10 lg:grid-cols-12">

            <div class="lg:col-span-7 xl:col-span-6">
                @foreach ($slides as $i => $slide)
                    <article
                        data-hero-panel="{{ $i }}"
                        @if ($i !== $initial) hidden @endif
                        aria-roledescription="slide"
                        aria-label="{{ $i + 1 }} of {{ count($slides) }}"
                    >
                        <p class="hero-enter inline-flex items-center gap-2 rounded-full border border-sand/25 bg-sand/10 px-4 py-2 text-sm text-white/90 backdrop-blur-md">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            {{ $slide['location'] }}
                        </p>

                        <h1 class="hero-enter mt-6 font-display text-5xl leading-[1.05] text-white sm:text-6xl lg:text-7xl xl:text-[5.5rem]" style="animation-delay: 80ms">
                            {{ $slide['title'] }}
                        </h1>

                        <p class="hero-enter mt-4 text-xl font-light text-white/85 sm:text-2xl" style="animation-delay: 160ms">
                            {{ $slide['subtitle'] }}
                        </p>

                        <div class="hero-enter mt-6 flex flex-wrap items-center gap-x-8 gap-y-3" style="animation-delay: 240ms">
                            <p class="flex items-center gap-2 text-white">
                                <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 2.6l2.9 5.9 6.5.9-4.7 4.6 1.1 6.4-5.8-3-5.8 3 1.1-6.4L2.6 9.4l6.5-.9L12 2.6Z"/>
                                </svg>
                                <span class="text-lg font-semibold">{{ $slide['rating'] }}</span>
                                <span class="text-sm text-white/60">&middot; {{ $reviews }} reviews</span>
                            </p>
                            <p class="text-xl font-semibold text-white sm:text-2xl">{{ $slide['price'] }}</p>
                        </div>

                        <div class="hero-enter mt-9 flex flex-wrap items-center gap-3 sm:gap-4" style="animation-delay: 320ms">
                            <a
                                href="{{ $slide['href'] }}"
                                class="inline-flex items-center rounded-full border border-sand/30 bg-sand/15 px-8 py-4 text-base font-medium text-white backdrop-blur-md transition hover:bg-sand/25"
                            >
                                Book Adventure
                            </a>

                            <a
                                href="/inquiry"
                                class="flex h-14 w-14 items-center justify-center rounded-full border border-sand/25 bg-sand/10 text-white backdrop-blur-md transition hover:bg-sand/20"
                            >
                                <span class="sr-only">Check availability</span>
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                                    <path d="M8 3v4M16 3v4M3 11h18"/>
                                </svg>
                            </a>

                            <button
                                type="button"
                                class="flex h-14 w-14 items-center justify-center rounded-full border border-sand/25 bg-sand/10 text-white backdrop-blur-md transition hover:bg-sand/20"
                            >
                                <span class="sr-only">Watch the film</span>
                                <svg class="ml-0.5 h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M7 4.5 19 12 7 19.5v-15Z"/>
                                </svg>
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Up next --}}
            <div class="hidden lg:col-span-5 lg:block xl:col-span-6">
                <div class="flex flex-col items-end gap-4">
                    <div class="flex gap-4" data-hero-thumbs>
                        @foreach ($slides as $i => $slide)
                            <button
                                type="button"
                                data-hero-thumb="{{ $i }}"
                                @if ($i === $initial) hidden @endif
                                class="group relative h-[105px] w-[155px] overflow-hidden rounded-xl border border-white/15 text-left transition duration-300 hover:-translate-y-1 hover:border-white/40"
                            >
                                <img src="{{ $slide['image'] }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                <span class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-transparent"></span>
                                <span class="absolute inset-x-0 bottom-0 px-3 pb-2.5 text-xs font-medium text-white">
                                    {{ $slide['title'] }}
                                </span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Progress --}}
                    <div class="flex gap-2 pr-1" role="tablist" aria-label="Choose destination">
                        @foreach ($slides as $i => $slide)
                            <button
                                type="button"
                                role="tab"
                                data-hero-dot="{{ $i }}"
                                aria-selected="{{ $i === $initial ? 'true' : 'false' }}"
                                class="h-1 rounded-full bg-white/35 transition-all duration-300 aria-selected:w-8 aria-selected:bg-white w-4"
                            >
                                <span class="sr-only">{{ $slide['title'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="absolute inset-x-0 bottom-8 z-10 px-6 sm:px-12 lg:px-20">
        <dl class="mx-auto grid max-w-5xl grid-cols-2 gap-3 sm:gap-4 md:grid-cols-4">
            @foreach ($stats as $stat)
                @php
                    // "100+" -> 100 with a "+" suffix; "4.9" -> 4.9 to one decimal;
                    // "1,200+" -> 1200 counted, then re-grouped as it renders.
                    preg_match('/^([\d.,]+)(.*)$/', $stat['value'], $parts);
                    $figure = $parts[1] ?? $stat['value'];
                    $suffix = $parts[2] ?? '';
                    $number = str_replace(',', '', $figure);
                    $decimals = strlen(substr(strrchr($number, '.') ?: '', 1));
                    $group = str_contains($figure, ',');
                @endphp

                <div class="rounded-2xl border border-sand/20 bg-sand/10 p-4 text-center backdrop-blur-md">
                    <dd
                        class="font-display text-3xl tabular-nums text-white lg:text-4xl"
                        data-count-to="{{ $number }}"
                        data-count-suffix="{{ $suffix }}"
                        data-count-decimals="{{ $decimals }}"
                        @if ($group) data-count-group @endif
                    >{{ $stat['value'] }}</dd>
                    <dt class="mt-1 text-xs text-white/70 lg:text-sm">{{ $stat['label'] }}</dt>
                </div>
            @endforeach
        </dl>
    </div>
</section>
