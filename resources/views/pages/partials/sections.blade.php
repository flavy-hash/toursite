{{--
    Body sections. Each row alternates the photo left/right; a section with no
    photo runs full width rather than leaving a hole beside it.
--}}
@if ($page->body_sections)
    <section class="bg-cream px-6 py-16 text-dark-brown sm:px-12 lg:px-20 lg:py-24">
        <div class="mx-auto max-w-7xl space-y-16 lg:space-y-24">
            @foreach ($page->body_sections as $index => $section)
                @if ($section['image'])
                    <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
                        <div class="{{ $index % 2 ? 'lg:order-2' : '' }}">
                            @if ($section['heading'])
                                <h2 class="font-display text-3xl lg:text-4xl">{{ $section['heading'] }}</h2>
                            @endif

                            @if ($section['body'])
                                <div class="mt-5 space-y-4 text-base leading-relaxed text-brown/80">
                                    @foreach (preg_split('/\R{2,}/', trim($section['body'])) as $paragraph)
                                        <p>{{ $paragraph }}</p>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="overflow-hidden rounded-3xl">
                            <img
                                src="{{ $section['image'] }}"
                                alt="{{ $section['heading'] }}"
                                loading="lazy"
                                class="aspect-[4/3] w-full object-cover"
                            >
                        </div>
                    </div>
                @else
                    <div class="mx-auto max-w-3xl">
                        @if ($section['heading'])
                            <h2 class="font-display text-3xl lg:text-4xl">{{ $section['heading'] }}</h2>
                        @endif

                        @if ($section['body'])
                            <div class="mt-5 space-y-4 text-base leading-relaxed text-brown/80">
                                @foreach (preg_split('/\R{2,}/', trim($section['body'])) as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </section>
@endif
