@php
    $video = \App\Models\HomeVideo::current();
@endphp

{{--
    YouTube panel, modelled on serengetiwildway.com's "FROM OUR CHANNEL"
    section: eyebrow, title, then a single 16:9 player in a rounded frame with
    a caption bar beneath it carrying the video's own title and a small
    monospaced line of detail.

    Skipped entirely when nothing is published, or when the stored id is not a
    real YouTube id — an empty black rectangle in the middle of the homepage
    looks worse than no section at all.
--}}
@if ($video && $video->hasValidId())
    {{--
        Continues the light-sand block that Stories and the awards badge sit
        in, rather than starting a new colour immediately under the badge.
        No top padding of its own: the awards section's pb-20 already provides
        the gap, and doubling them leaves the badge stranded.
    --}}
    <section class="bg-light-sand px-6 pb-20 pt-0 text-dark-brown sm:px-12 lg:px-20 lg:pb-28">
        <div class="mx-auto max-w-7xl text-center">
            @if ($video->eyebrow)
                <p class="text-xs uppercase tracking-[0.22em] text-brown/60">{{ $video->eyebrow }}</p>
            @endif

            @if ($video->heading)
                <h2 class="mt-3 font-display text-3xl lg:text-4xl">{{ $video->heading }}</h2>
            @endif

            <div class="yt-frame mt-10">
                <div class="yt-ratio">
                    {{--
                        loading="lazy" matters here: an eager YouTube iframe
                        pulls in several hundred KB of player before anyone has
                        decided to watch it.
                    --}}
                    <iframe
                        src="{{ $video->embedUrl() }}"
                        title="{{ $video->video_title ?: $video->heading }}"
                        allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen
                        loading="lazy"
                    ></iframe>
                </div>

                @if ($video->video_title || $video->caption)
                    <div class="yt-cap">
                        @if ($video->video_title)
                            <h3>{{ $video->video_title }}</h3>
                        @endif

                        @if ($video->caption)
                            <p>{{ $video->caption }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
