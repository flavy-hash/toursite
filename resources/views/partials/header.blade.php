@php
    $nav = config('site.nav');
    $contact = config('site.contact');
    $current = '/' . ltrim(request()->path(), '/');

    $whatsapp = 'https://wa.me/' . $contact['whatsapp'] . '?text=' . rawurlencode($contact['whatsapp_message']);
@endphp

<header data-site-header class="fixed inset-x-0 top-0 z-[100] transition-colors duration-300">
    {{-- Three tracks so the nav centres on the header, not on whatever the logo leaves over. --}}
    <div class="mx-auto grid max-w-[1600px] grid-cols-[1fr_auto_1fr] items-center gap-6 px-6 py-5 lg:px-10">

        {{-- Brand --}}
        <a href="/" class="justify-self-start leading-none">
            <span class="block font-display text-xl text-white lg:text-2xl">
                {{ config('site.brand.name') }}
            </span>
            <span class="mt-0.5 block text-[10px] uppercase tracking-[0.28em] text-white/70">
                {{ config('site.brand.suffix') }}
            </span>
        </a>

        {{-- Primary navigation. Becomes a drawer below lg, hidden entirely on phones. --}}
        <nav id="nav-menu" data-nav-menu class="nav-menu justify-self-center" aria-label="Primary">
            @foreach ($nav as $item)
                @php $isCurrent = $current === $item['path']; @endphp

                <div class="nav-item {{ isset($item['panel']) ? 'has-mega' : '' }}">
                    <a
                        href="{{ $item['path'] }}"
                        @if ($isCurrent) aria-current="page" data-current="true" @endif
                        class="nav-link"
                    >
                        {{ $item['name'] }}
                        @isset($item['panel'])
                            <x-icon name="chevron" class="nav-chevron h-3.5 w-3.5" />
                        @endisset
                    </a>

                    @isset($item['panel'])
                        @php $panel = $item['panel']; @endphp

                        {{-- Mega panel: pinned to the viewport centre, not to this trigger. --}}
                        <div class="mega">
                            {{-- Rail: every way into this section --}}
                            <div class="mega-side">
                                @foreach ($panel['rail'] as $link)
                                    <a href="{{ $link['path'] }}">{{ $link['name'] }}</a>
                                @endforeach
                            </div>

                            {{-- Body: the pitch --}}
                            <div class="mega-main">
                                <h3 class="font-display text-2xl text-cream">{{ $panel['heading'] }}</h3>
                                <p class="mega-copy">{{ $panel['copy'] }}</p>

                                <a href="{{ $panel['cta']['path'] }}" class="mega-cta">
                                    {{ $panel['cta']['label'] }}
                                    <x-icon name="arrow" class="h-4 w-4" />
                                </a>
                            </div>

                            {{-- Thumb --}}
                            <div class="mega-thumb" aria-hidden="true">
                                <img src="{{ $panel['image'] }}" alt="" loading="lazy">
                            </div>
                        </div>
                    @endisset
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
                <x-icon name="whatsapp" class="h-5 w-5" />
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
