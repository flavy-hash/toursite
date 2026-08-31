@php
    $pillars = config('site.pillars');
@endphp

<section class="relative overflow-hidden bg-dark-brown px-6 py-20 sm:px-12 lg:px-20 lg:py-28">
    {{-- Soft warm bloom so the flat brown band doesn't read as a dead panel. --}}
    <span class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-sand/10 blur-3xl" aria-hidden="true"></span>

    <div class="relative mx-auto grid max-w-7xl gap-14 lg:grid-cols-12 lg:items-start">

        <div class="lg:col-span-5">
            <p class="text-xs uppercase tracking-[0.22em] text-sand/70">Why Travel With Us</p>
            <h2 class="mt-3 font-display text-4xl leading-tight text-cream lg:text-5xl">
                Crafting extraordinary journeys
            </h2>
            <p class="mt-5 max-w-md text-base font-light leading-relaxed text-white/70">
                {{ config('site.brand.name') }} is built on local expertise and a simple rule — we only sell trips we would take ourselves.
            </p>

            <a href="/about" class="mt-8 inline-flex items-center gap-2 rounded-full border border-sand/40 px-6 py-3 text-xs uppercase tracking-[0.14em] text-cream transition-colors hover:bg-sand hover:text-dark-brown">
                Discover Our Story
                <x-icon name="arrow" class="h-4 w-4" />
            </a>
        </div>

        <div class="grid gap-5 lg:col-span-7">
            @foreach ($pillars as $pillar)
                <div class="flex gap-5 rounded-2xl border border-sand/15 bg-sand/5 p-6 transition-colors hover:border-sand/35">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-sand/30 text-sand">
                        <x-icon :name="$pillar['icon']" class="h-5 w-5" />
                    </span>

                    <div>
                        <h3 class="font-display text-xl text-cream">{{ $pillar['title'] }}</h3>
                        <p class="mt-2 text-sm font-light leading-relaxed text-white/70">{{ $pillar['copy'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
