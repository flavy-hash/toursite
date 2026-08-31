@php
    $stories = config('site.stories');
@endphp

<section class="bg-light-sand px-6 py-20 text-dark-brown sm:px-12 lg:px-20 lg:py-28">
    <div class="mx-auto max-w-7xl">

        <div class="max-w-xl">
            <p class="text-xs uppercase tracking-[0.22em] text-brown/60">Traveller Stories</p>
            <h2 class="mt-3 font-display text-4xl leading-tight lg:text-5xl">
                Voices from the trail
            </h2>
        </div>

        <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($stories as $story)
                <figure class="flex h-full flex-col rounded-3xl border border-brown/10 bg-cream p-7">
                    <x-icon name="quote" class="h-7 w-7 text-brown/25" />

                    <blockquote class="mt-4 flex-1 text-[15px] font-light leading-relaxed text-brown/85">
                        {{ $story['quote'] }}
                    </blockquote>

                    <x-stars :rating="$story['rating']" class="mt-5 text-brown" />

                    <figcaption class="mt-4 border-t border-brown/10 pt-4">
                        <p class="font-display text-lg">{{ $story['name'] }}</p>
                        <p class="text-xs text-brown/60">{{ $story['from'] }}</p>
                        <p class="mt-2 text-xs text-brown/70">
                            {{ $story['trip'] }} &middot; {{ $story['when'] }}
                        </p>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
