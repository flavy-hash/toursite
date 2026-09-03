@props(['tone' => 'light'])

@php
    $profiles = array_filter(config('site.social', []));

    // Names read by screen readers, and used as the visible link title.
    $labels = [
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'tiktok' => 'TikTok',
        'youtube' => 'YouTube',
        'x' => 'X',
    ];

    // Brand colours on the pale sections; monochrome on the dark footer, where
    // five saturated logos would fight the type around them.
    $brand = [
        'instagram' => '#E1306C',
        'facebook' => '#1877F2',
        'tiktok' => '#111111',
        'youtube' => '#FF0000',
        'x' => '#111111',
    ];
@endphp

@if ($profiles)
    <ul {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-3']) }}>
        @foreach ($profiles as $network => $url)
            <li>
                <a
                    href="{{ $url }}"
                    target="_blank"
                    {{-- noopener protects the opener; nofollow keeps link equity
                         from leaking to profiles we do not control. --}}
                    rel="noopener noreferrer nofollow"
                    title="{{ $labels[$network] ?? ucfirst($network) }}"
                    @class([
                        'flex h-12 w-12 items-center justify-center rounded-full border transition duration-200 hover:-translate-y-0.5',
                        'border-brown/15 bg-white hover:border-brown/35 hover:shadow-md' => $tone === 'light',
                        'border-sand/25 text-sand hover:border-sand hover:bg-sand hover:text-dark-brown' => $tone === 'dark',
                    ])
                    @style(['color: ' . ($brand[$network] ?? '#4a2e1d') => $tone === 'light'])
                >
                    <span class="sr-only">{{ $labels[$network] ?? ucfirst($network) }}</span>
                    <x-social-icon :name="$network" class="h-5 w-5" />
                </a>
            </li>
        @endforeach
    </ul>
@endif
