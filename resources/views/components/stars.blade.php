@props(['rating' => 5])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5']) }} role="img" aria-label="{{ $rating }} out of 5">
    @for ($i = 1; $i <= 5; $i++)
        <svg class="h-4 w-4 fill-current {{ $i <= $rating ? '' : 'opacity-25' }}" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 2.6l2.9 5.9 6.5.9-4.7 4.6 1.1 6.4-5.8-3-5.8 3 1.1-6.4L2.6 9.4l6.5-.9L12 2.6Z"/>
        </svg>
    @endfor
</span>
