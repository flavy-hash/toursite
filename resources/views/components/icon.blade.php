@props(['name'])

<svg
    {{ $attributes->merge(['class' => 'h-5 w-5']) }}
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.6"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
>
    @switch($name)
        @case('home')
            <path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/>
            @break

        @case('pin')
            <path d="M12 21s-6-5.1-6-10a6 6 0 1 1 12 0c0 4.9-6 10-6 10z"/><circle cx="12" cy="11" r="2.2"/>
            @break

        @case('compass')
            <circle cx="12" cy="12" r="9"/><path d="M15.5 8.5l-2 5-5 2 2-5z"/>
            @break

        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 11h18"/>
            @break

        @case('info')
            <circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/>
            @break

        @case('mail')
            <path d="M4 6h16v12H4z"/><path d="M4 7l8 6 8-6"/>
            @break

        @case('chevron')
            <path d="m6 9 6 6 6-6"/>
            @break

        @case('arrow')
            <path d="M5 12h14M13 6l6 6-6 6"/>
            @break

        @case('clock')
            <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
            @break

        @case('users')
            <path d="M16 19v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 17.5V19"/>
            <circle cx="10" cy="8" r="3.2"/><path d="M20 19v-1.5a3.5 3.5 0 0 0-2.6-3.4"/>
            @break

        @case('gauge')
            <path d="M12 14l4-4"/><path d="M4.5 18a9 9 0 1 1 15 0"/>
            @break

        @case('mountain')
            <path d="M3 20l6-11 4 6 3-4 5 9z"/>
            @break

        @case('wave')
            <path d="M3 18c3-1 5 1 9 0s6 1 9 0"/><circle cx="12" cy="8" r="4"/>
            @break

        @case('whatsapp')
            <path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2z"/>
            <path d="M8.5 9.2c0-.4.2-.8.5-1 .2-.1.5-.1.7 0 .2.1.6 1 .8 1.4.1.2 0 .4-.1.5l-.4.5c-.1.2-.2.3 0 .5.5.9 1.3 1.6 2.3 2 .2.1.4 0 .5-.1l.5-.6c.1-.2.3-.2.5-.1.4.2 1.3.6 1.4.8v.6c-.1.5-.6 1-1.2 1.1-.4.1-.9.1-1.4-.1-2-.7-3.6-2.3-4.3-4.3-.1-.4-.2-.8-.2-1.2z"/>
            @break

        @case('quote')
            <path d="M9 7H5.5A1.5 1.5 0 0 0 4 8.5V12h5V7zM9 12c0 3-1.6 4.6-4 5"/>
            <path d="M20 7h-3.5A1.5 1.5 0 0 0 15 8.5V12h5V7zM20 12c0 3-1.6 4.6-4 5"/>
            @break
    @endswitch
</svg>
