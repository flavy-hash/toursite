@foreach ($tours as $tour)
    <div style="margin:0 0 16px; border:1px solid #e6ded2; border-radius:14px; overflow:hidden;">
        @if ($tour->image_url)
            <a href="{{ route('tours.show', $tour->slug) }}">
                <img src="{{ url($tour->image_url) }}" alt="{{ $tour->name }}" width="552"
                     style="display:block; width:100%; height:auto; border:0;">
            </a>
        @endif

        <div style="padding:18px 20px;">
            <p style="margin:0 0 6px; font-size:11px; letter-spacing:2px; text-transform:uppercase; color:#a08f7c;">
                {{ $tour->category }} &middot; {{ $tour->days }}
            </p>

            <p style="margin:0 0 8px; font-size:19px; color:#3a2418;">
                <a href="{{ route('tours.show', $tour->slug) }}" style="color:#3a2418; text-decoration:none;">
                    {{ $tour->name }}
                </a>
            </p>

            @if ($tour->tagline)
                <p style="margin:0 0 14px; font-size:14px; line-height:1.6; color:#6b5a49;">{{ $tour->tagline }}</p>
            @endif

            <p style="margin:0;">
                <a href="{{ route('tours.show', $tour->slug) }}"
                   style="display:inline-block; padding:10px 20px; background:#4a2e1d; color:#f0e6d2;
                          border-radius:999px; font-size:12px; letter-spacing:1px; text-transform:uppercase;
                          text-decoration:none;">
                    From {{ $tour->price }} &rarr;
                </a>
            </p>
        </div>
    </div>
@endforeach
