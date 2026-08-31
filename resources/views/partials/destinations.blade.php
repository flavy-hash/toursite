@php
    $destinations = config('site.destinations');
@endphp

<section class="bg-light-sand px-6 py-20 text-dark-brown sm:px-12 lg:px-20 lg:py-28">
    <div class="mx-auto max-w-7xl">

        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.22em] text-brown/60">Iconic Destinations</p>
                <h2 class="mt-3 max-w-xl font-display text-4xl leading-tight lg:text-5xl">
                    Where to go in Tanzania
                </h2>
            </div>

            <a href="/destinations" class="group inline-flex items-center gap-2 border-b border-brown/30 pb-1 text-sm font-medium transition-colors hover:border-brown">
                See All Destinations
                <x-icon name="arrow" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
            </a>
        </div>

        {{-- The lead destination takes a double-width tile; the rest follow at half. --}}
        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($destinations as $destination)
                <a
                    href="{{ $destination['href'] }}"
                    class="group relative overflow-hidden rounded-3xl {{ ($destination['featured'] ?? false) ? 'sm:col-span-2 lg:row-span-2 min-h-[22rem] lg:min-h-[34rem]' : 'min-h-[16rem] lg:min-h-[16.5rem]' }}"
                >
                    <img
                        src="{{ $destination['image'] }}"
                        alt="{{ $destination['name'] }}"
                        loading="lazy"
                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                    >
                    <span class="absolute inset-0 bg-gradient-to-t from-dark-brown/90 via-dark-brown/25 to-transparent"></span>

                    <span class="absolute left-5 top-5 rounded-full border border-white/25 bg-white/10 px-3 py-1 text-[11px] uppercase tracking-[0.14em] text-white backdrop-blur-md">
                        {{ $destination['region'] }}
                    </span>

                    <div class="absolute inset-x-0 bottom-0 p-5 text-white lg:p-6">
                        <h3 class="font-display {{ ($destination['featured'] ?? false) ? 'text-3xl lg:text-4xl' : 'text-2xl' }}">
                            {{ $destination['name'] }}
                        </h3>

                        <p class="mt-2 max-w-md text-sm font-light leading-relaxed text-white/75">
                            {{ $destination['tagline'] }}
                        </p>

                        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-white/70">
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon name="calendar" class="h-4 w-4" />
                                {{ $destination['best'] }}
                            </span>
                            <span class="font-semibold text-white">{{ $destination['price'] }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
