@php
    $items = config('site.bottom_nav');
    $current = '/' . ltrim(request()->path(), '/');
@endphp

{{--
    Phone-only tab bar. Below the sm breakpoint this replaces the drawer
    entirely, so the header keeps only the logo.
--}}
<nav class="bottom-nav" aria-label="Mobile">
    <div class="bottom-nav-row">
        @foreach ($items as $item)
            <a
                href="{{ $item['path'] }}"
                @class(['is-active' => $current === $item['path']])
                @if ($current === $item['path']) aria-current="page" @endif
            >
                <x-icon :name="$item['icon']" class="h-[19px] w-[19px]" />
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
