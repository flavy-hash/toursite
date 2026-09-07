@php
    $items = \App\Models\NavItem::active()->location(\App\Models\NavItem::BOTTOM)->ordered()->get();
    $current = '/' . ltrim(request()->path(), '/');
@endphp

{{--
    Phone-only tab bar. Below the md breakpoint this replaces the drawer
    entirely, so the header keeps only the logo.
--}}
@if ($items->isNotEmpty())
    <nav class="bottom-nav" aria-label="Mobile">
        <div class="bottom-nav-row">
            @foreach ($items as $item)
                @php $isCurrent = $current === \Illuminate\Support\Str::before($item->path, '?'); @endphp

                <a
                    href="{{ $item->path }}"
                    @class(['is-active' => $isCurrent])
                    @if ($isCurrent) aria-current="page" @endif
                >
                    <x-ui-icon :name="$item->icon ?: 'compass'" class="h-[19px] w-[19px]" />
                    <span>{{ $item->label }}</span>
                </a>
            @endforeach
        </div>
    </nav>
@endif
