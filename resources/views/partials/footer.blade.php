@php
    $columns = config('site.footer');
    $contact = config('site.contact');
@endphp

<footer class="bg-dark-brown px-6 pb-10 pt-20 sm:px-12 lg:px-20">
    <div class="mx-auto max-w-7xl">

        <div class="grid gap-12 lg:grid-cols-12">

            <div class="lg:col-span-4">
                <p class="font-display text-2xl text-cream">{{ config('site.brand.name') }}</p>
                <p class="mt-1 text-[10px] uppercase tracking-[0.28em] text-sand/70">
                    {{ config('site.brand.suffix') }}
                </p>

                <p class="mt-5 max-w-xs text-sm font-light leading-relaxed text-white/60">
                    {{ config('site.brand.tagline') }}
                </p>

                <ul class="mt-6 space-y-2 text-sm text-white/70">
                    <li>
                        <a href="mailto:{{ $contact['email'] }}" class="transition-colors hover:text-sand">{{ $contact['email'] }}</a>
                    </li>
                    <li>
                        <a href="tel:{{ preg_replace('/\s+/', '', $contact['phone']) }}" class="transition-colors hover:text-sand">{{ $contact['phone'] }}</a>
                    </li>
                    <li class="text-white/55">{{ $contact['address'] }}</li>
                </ul>
            </div>

            <div class="grid gap-10 sm:grid-cols-3 lg:col-span-8">
                @foreach ($columns as $column)
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.2em] text-sand">{{ $column['heading'] }}</p>
                        <ul class="mt-4 space-y-2.5">
                            @foreach ($column['items'] as $link)
                                <li>
                                    <a href="{{ $link['path'] }}" class="text-sm text-white/65 transition-colors hover:text-white">
                                        {{ $link['name'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-14 flex flex-col gap-3 border-t border-sand/15 pt-6 text-xs text-white/45 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} {{ config('site.brand.name') }} {{ config('site.brand.suffix') }}. All rights reserved.</p>
            <p>{{ $contact['address'] }}</p>
        </div>
    </div>
</footer>
