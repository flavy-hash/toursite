@php $profiles = array_filter(config('site.social', [])); @endphp

@if ($profiles)
    {{-- Skipped entirely when no profiles are configured. --}}
    <section class="bg-cream px-6 py-16 text-center text-dark-brown sm:px-12 lg:px-20">
        <div class="mx-auto max-w-2xl">
            <p class="text-xs uppercase tracking-[0.22em] text-brown/60">Follow the journey</p>

            <h2 class="mt-3 font-display text-3xl lg:text-4xl">
                See where we are this week
            </h2>

            <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-brown/70">
                Sightings, camp life and the odd sunset, posted from the field.
            </p>

            <x-social-links class="mt-8 justify-center" />
        </div>
    </section>
@endif
