@php
    // Admin-managed. Falls back to nothing rather than erroring if the table
    // is empty — the brand and CTA still render.
    $nav = \App\Models\NavItem::active()->location(\App\Models\NavItem::HEADER)->ordered()->get();

    $contact = config('site.contact');
    $current = '/' . ltrim(request()->path(), '/');

    $whatsapp = 'https://wa.me/' . $contact['whatsapp'] . '?text=' . rawurlencode($contact['whatsapp_message']);
@endphp

<header data-site-header class="fixed inset-x-0 top-0 z-[100] transition-colors duration-300">
    {{-- Three tracks so the nav centres on the header, not on whatever the logo leaves over. --}}
    <div class="mx-auto grid max-w-[1600px] grid-cols-[1fr_auto_1fr] items-center gap-6 px-6 py-5 lg:px-10">

        {{--
            Brand: the full horizontal lockup — mark, TWINS, and "African
            Travel" as one piece of artwork. Supplied white on transparency,
            which suits the header in both its states (transparent over the
            hero, dark brown once scrolled), so it needs no recolouring.

            alt carries the brand name because the image is now the only place
            it appears up here.
        --}}
        <a href="/" class="justify-self-start leading-none">
            <img
                src="{{ asset('assets/images/logo twins mount side.png') }}"
                alt="{{ config('site.brand.name') }} {{ config('site.brand.suffix') }}"
                width="896"
                height="164"
                class="h-9 w-auto lg:h-11"
            >
        </a>

        {{-- Primary navigation. Becomes a drawer below lg, hidden entirely on phones. --}}
        <nav id="nav-menu" data-nav-menu class="nav-menu justify-self-center" aria-label="Primary">
            @foreach ($nav as $item)
                @php $isCurrent = $current === \Illuminate\Support\Str::before($item->path, '?'); @endphp

                <div class="nav-item {{ $item->hasPanel() ? 'has-mega' : '' }}">
                    <a
                        href="{{ $item->path }}"
                        @if ($isCurrent) aria-current="page" data-current="true" @endif
                        class="nav-link"
                    >
                        {{ $item->label }}
                        @if ($item->hasPanel())
                            <x-ui-icon name="chevron" class="nav-chevron h-3.5 w-3.5" />
                        @endif
                    </a>

                    @if ($item->hasPanel())
                        {{-- Mega panel: pinned to the viewport centre, not to this trigger. --}}
                        <div class="mega">
                            {{-- Rail: every way into this section --}}
                            <div class="mega-side">
                                @foreach ($item->railLinks() as $link)
                                    <a href="{{ $link['path'] }}">{{ $link['name'] }}</a>
                                @endforeach
                            </div>

                            {{-- Body: the pitch --}}
                            <div class="mega-main">
                                <h3 class="font-display text-2xl text-cream">{{ $item->panel_heading }}</h3>

                                @if ($item->panel_copy)
                                    <p class="mega-copy">{{ $item->panel_copy }}</p>
                                @endif

                                @if ($item->panel_cta_label)
                                    <a href="{{ $item->panel_cta_path ?: $item->path }}" class="mega-cta">
                                        {{ $item->panel_cta_label }}
                                        <x-ui-icon name="arrow" class="h-4 w-4" />
                                    </a>
                                @endif
                            </div>

                            {{-- Thumb --}}
                            @if ($item->panel_image_url)
                                <div class="mega-thumb" aria-hidden="true">
                                    <img src="{{ $item->panel_image_url }}" alt="" loading="lazy">
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </nav>

        <div class="col-start-3 flex items-center justify-end gap-3 justify-self-end">
            {{-- Enquiry CTA. Label collapses to the icon once space gets tight. --}}
            <a
                href="{{ $whatsapp }}"
                target="_blank"
                rel="noopener"
                class="wa-btn"
            >
                <x-ui-icon name="whatsapp" class="h-5 w-5" />
                <span>WhatsApp</span>
            </a>

            {{-- Drawer toggle: tablet only. Phones get the bottom bar instead. --}}
            <button
                type="button"
                id="hamburger"
                data-menu-toggle
                aria-controls="nav-menu"
                aria-expanded="false"
                class="hamburger"
            >
                <span class="sr-only">Menu</span>
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>
