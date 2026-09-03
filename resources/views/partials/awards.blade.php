@php $awards = array_filter(config('site.awards', [])); @endphp

@if ($awards)
    {{-- Sits directly under the traveller reviews, where the social proof is. --}}
    <section class="bg-light-sand px-6 pb-20 pt-2 text-center text-dark-brown sm:px-12 lg:px-20">
        <div class="mx-auto max-w-3xl">
            <ul class="flex flex-wrap items-center justify-center gap-6">
                @foreach ($awards as $award)
                    <li>
                        <a
                            href="{{ $award['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer nofollow"
                            title="{{ $award['name'] }}"
                            class="group flex h-32 w-32 items-center justify-center rounded-full border-2 border-amber-400/70 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-amber-400 hover:shadow-lg"
                        >
                            <span class="sr-only">{{ $award['name'] }} — view our profile</span>
                            <img
                                src="{{ $award['image'] }}"
                                alt="{{ $award['name'] }}"
                                loading="lazy"
                                class="h-full w-full object-contain"
                            >
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
