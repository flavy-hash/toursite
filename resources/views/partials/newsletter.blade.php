<section id="newsletter" class="relative overflow-hidden bg-brown px-6 py-20 text-center sm:px-12 lg:px-20 lg:py-24">
    {{-- Low sun behind the copy, echoing the hero's warm wash. --}}
    <span
        class="pointer-events-none absolute bottom-[-46%] left-1/2 aspect-square w-[min(70vw,680px)] -translate-x-1/2 rounded-full"
        style="background: radial-gradient(circle at 50% 30%, color-mix(in srgb, var(--color-sand) 45%, transparent), transparent 70%)"
        aria-hidden="true"
    ></span>

    <div class="relative mx-auto max-w-2xl">
        <p class="text-xs uppercase tracking-[0.22em] text-sand/70">Stay Updated</p>

        <h2 class="mt-3 font-display text-4xl leading-tight text-cream lg:text-5xl">
            New adventures, first
        </h2>

        <p class="mx-auto mt-4 max-w-md text-base font-light leading-relaxed text-white/70">
            Occasional notes on new routes, quiet season openings and where the herds are heading. No noise.
        </p>

        @if (session('subscribed'))
            {{-- Confirmation replaces the form, so there is nothing to submit twice. --}}
            <div
                class="mx-auto mt-9 flex max-w-md items-center justify-center gap-3 rounded-full border border-sand/40 bg-sand/15 px-6 py-4 text-sm text-cream backdrop-blur-md"
                role="status"
            >
                <x-ui-icon name="mail" class="h-5 w-5 shrink-0" />
                You&rsquo;re on the list — we&rsquo;ll be in touch when something good comes up.
            </div>
        @else
            <form action="{{ route('subscribe') }}" method="POST" class="mx-auto mt-9 max-w-md">
                @csrf

                <div class="flex flex-col gap-3 sm:flex-row">
                    <label for="newsletter-email" class="sr-only">Email address</label>
                    <input
                        id="newsletter-email"
                        type="email"
                        name="email"
                        required
                        autocomplete="email"
                        placeholder="Enter your email"
                        value="{{ old('email') }}"
                        @class([
                            'w-full rounded-full border bg-dark-brown/40 px-6 py-4 text-sm text-cream placeholder:text-white/45 backdrop-blur-md focus:outline-none',
                            'border-sand/30 focus:border-sand' => ! $errors->newsletter->has('email'),
                            'border-red-400/70' => $errors->newsletter->has('email'),
                        ])
                        @if ($errors->newsletter->has('email')) aria-invalid="true" aria-describedby="newsletter-error" @endif
                    >

                    {{-- Honeypot. Hidden from people, irresistible to bots. --}}
                    <div class="hidden" aria-hidden="true">
                        <label for="newsletter-website">Website</label>
                        <input id="newsletter-website" type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <button
                        type="submit"
                        class="shrink-0 rounded-full bg-sand px-8 py-4 text-xs uppercase tracking-[0.14em] text-dark-brown transition-colors hover:bg-cream"
                    >
                        Subscribe
                    </button>
                </div>

                @if ($errors->newsletter->any())
                    <p id="newsletter-error" class="mt-3 text-sm text-red-200">
                        {{ $errors->newsletter->first() }}
                    </p>
                @endif
            </form>
        @endif
    </div>
</section>
