@php
    $nav = config('site.nav');
    $current = '/' . ltrim(request()->path(), '/');
@endphp

<header
    data-site-header
    class="fixed inset-x-0 top-0 z-50 transition-colors duration-300"
>
    <div class="mx-auto flex max-w-[1600px] items-center justify-between gap-8 px-6 py-5 lg:px-10">

        {{-- Brand --}}
        <a href="/" class="shrink-0 leading-none">
            <span class="block font-display text-xl text-white lg:text-2xl">
                {{ config('site.brand.name') }}
            </span>
            <span class="mt-0.5 block text-[10px] uppercase tracking-[0.28em] text-white/70">
                {{ config('site.brand.suffix') }}
            </span>
        </a>

        {{-- Desktop navigation --}}
        <nav class="hidden items-center gap-8 lg:flex" aria-label="Primary">
            @foreach ($nav as $item)
                @php $isCurrent = $current === $item['path']; @endphp

                @if (empty($item['columns']))
                    <a
                        href="{{ $item['path'] }}"
                        @if ($isCurrent) aria-current="page" data-current="true" @endif
                        class="nav-underline relative text-[15px] font-medium text-white/90 transition-colors hover:text-white"
                    >
                        {{ $item['name'] }}
                    </a>
                @else
                    <div class="group relative">
                        <a
                            href="{{ $item['path'] }}"
                            @if ($isCurrent) aria-current="page" data-current="true" @endif
                            class="nav-underline relative flex items-center gap-1.5 text-[15px] font-medium text-white/90 transition-colors hover:text-white"
                        >
                            {{ $item['name'] }}
                            <svg class="h-3.5 w-3.5 transition-transform duration-200 group-hover:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </a>

                        {{-- Mega menu. Wide panels anchor right so they stay inside the viewport. --}}
                        <div class="invisible absolute top-full z-50 pt-5 opacity-0 transition-all duration-200 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100 {{ count($item['columns']) > 2 ? 'right-0' : 'left-1/2 -translate-x-1/2' }}">
                            <div
                                class="rounded-2xl border border-sand/20 p-6 shadow-2xl backdrop-blur-xl"
                                style="background-color: color-mix(in srgb, var(--color-dark-brown) 92%, transparent);"
                            >
                                @isset($item['blurb'])
                                    <p class="mb-5 max-w-md text-xs uppercase tracking-[0.18em] text-sand/70">
                                        {{ $item['blurb'] }}
                                    </p>
                                @endisset

                                <div class="grid gap-x-10 gap-y-6 {{ count($item['columns']) > 2 ? 'grid-cols-4' : 'grid-cols-2' }}">
                                    @foreach ($item['columns'] as $column)
                                        <div class="min-w-[13rem]">
                                            <p class="mb-3 font-display text-base text-cream">{{ $column['heading'] }}</p>
                                            <ul class="space-y-2">
                                                @foreach ($column['items'] as $link)
                                                    <li>
                                                        <a href="{{ $link['path'] }}" class="block text-sm text-white/70 transition-colors hover:text-white">
                                                            {{ $link['name'] }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>

        {{-- Mobile toggle --}}
        <button
            type="button"
            data-menu-toggle
            aria-controls="mobile-menu"
            aria-expanded="false"
            class="flex h-11 w-11 items-center justify-center rounded-full border border-sand/25 bg-sand/10 text-white backdrop-blur-md lg:hidden"
        >
            <span class="sr-only">Toggle mobile menu</span>
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M4 7h16M4 12h16M4 17h16"/>
            </svg>
        </button>
    </div>

    {{-- Mobile menu --}}
    <div id="mobile-menu" hidden class="border-t border-sand/15 bg-dark-brown/95 backdrop-blur-xl lg:hidden">
        <nav class="max-h-[75vh] space-y-1 overflow-y-auto px-6 py-5" aria-label="Mobile">
            @foreach ($nav as $item)
                <a href="{{ $item['path'] }}" class="block py-2 font-display text-lg text-cream">
                    {{ $item['name'] }}
                </a>
                @isset($item['columns'])
                    <div class="mb-3 space-y-1 border-l border-sand/20 pl-4">
                        @foreach ($item['columns'] as $column)
                            @foreach ($column['items'] as $link)
                                <a href="{{ $link['path'] }}" class="block py-1 text-sm text-white/70">{{ $link['name'] }}</a>
                            @endforeach
                        @endforeach
                    </div>
                @endisset
            @endforeach
        </nav>
    </div>
</header>
