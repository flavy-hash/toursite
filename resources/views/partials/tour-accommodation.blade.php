@php $stays = $tour->accommodations; @endphp

@if ($stays->isNotEmpty())
    <h2 class="mt-14 font-display text-3xl lg:text-4xl">Where you&rsquo;ll stay</h2>

    <div class="stay-grid mt-6">
        @foreach ($stays as $stay)
            @php
                // Every photo of one property shares a group, so the viewer's
                // arrows walk that property rather than the whole page.
                $group = 'stay-' . $stay->id;
                $shots = array_values(array_filter(array_merge([$stay->image_url], $stay->gallery_urls)));
            @endphp

            <article class="stay">
                @if ($shots)
                    <a
                        class="shot"
                        href="{{ $shots[0] }}"
                        target="_blank"
                        rel="noopener"
                        data-lbx="{{ $group }}"
                        data-caption="{{ $stay->name }}"
                        aria-label="View photos of {{ $stay->name }}"
                    >
                        <img src="{{ $shots[0] }}" alt="{{ $stay->name }}" loading="lazy">
                    </a>

                    @if (count($shots) > 1)
                        {{-- The rest of the group. Linked so they join the
                             viewer, and so they work with JavaScript off. --}}
                        <div class="stay-gal">
                            @foreach (array_slice($shots, 1) as $shot)
                                <a
                                    href="{{ $shot }}"
                                    target="_blank"
                                    rel="noopener"
                                    data-lbx="{{ $group }}"
                                    data-caption="{{ $stay->name }}"
                                    aria-label="View photos of {{ $stay->name }}"
                                >
                                    <img src="{{ $shot }}" alt="" loading="lazy">
                                </a>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="shot is-empty" aria-hidden="true"><span>Photo to come</span></div>
                @endif

                <div class="stay-txt">
                    <div class="flex flex-wrap items-center gap-x-2">
                        <h3 class="stay-name font-display leading-snug">{{ $stay->name }}</h3>

                        @if ($stay->rating)
                            <span class="text-xs text-brown/55">{{ str_repeat('★', $stay->rating) }}</span>
                        @endif
                    </div>

                    <p class="stay-where">
                        {{ $stay->type }}@if ($stay->location) &middot; {{ $stay->location }} @endif
                    </p>

                    @if ($stay->description)
                        <p class="mt-2.5 text-[13px] font-light leading-relaxed text-brown/75">
                            {{ Str::limit($stay->description, 120) }}
                        </p>
                    @endif

                    <div class="stay-meta">
                        <span class="stay-chip">{{ $stay->levelLabel() }}</span>

                        @if ($stay->pivot->nights)
                            <span class="stay-chip">
                                {{ $stay->pivot->nights }} {{ Str::plural('night', $stay->pivot->nights) }}
                            </span>
                        @endif

                        @if ($stay->board_basis)
                            <span class="stay-chip">{{ $stay->board_basis }}</span>
                        @endif

                        @if (count($shots) > 1)
                            <span class="stay-chip">{{ count($shots) }} photos</span>
                        @endif
                    </div>

                    @if ($stay->amenities)
                        <ul class="mt-3 flex flex-wrap gap-x-3 gap-y-1 text-[11px] text-brown/55">
                            @foreach (array_slice($stay->amenities, 0, 5) as $amenity)
                                <li>{{ $amenity }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($stay->price_impact)
                        <p class="mt-2.5 text-[11px] font-semibold text-dark-brown">{{ $stay->price_impact }}</p>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
@endif
