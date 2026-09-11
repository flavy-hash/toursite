@extends('layouts.app')

@section('title', $page->metaTitle())
@section('description', $page->metaDescription())

@push('schema')
    <x-seo-breadcrumbs :trail="[
        ['name' => 'About Us', 'url' => route('about')],
        ['name' => $page->title, 'url' => $page->url()],
    ]" />
@endpush

@section('content')

    @include('pages.partials.header', [
        'crumb' => ['About Us' => route('about'), $page->title => null],
    ])

    @include('pages.partials.sections')

    {{-- The team itself. Skipped entirely rather than showing an empty grid,
         so the page reads sensibly before anyone has been added. --}}
    @if ($members->isNotEmpty())
        <section class="bg-cream px-6 py-16 text-dark-brown sm:px-12 lg:px-20 lg:py-24">
            <div class="mx-auto max-w-7xl">
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($members as $member)
                        <article class="overflow-hidden rounded-3xl border border-brown/12 bg-light-sand">
                            @if ($member->photo_url)
                                <img
                                    src="{{ $member->photo_url }}"
                                    alt="{{ $member->name }}"
                                    loading="lazy"
                                    class="aspect-[4/5] w-full object-cover object-top"
                                >
                            @else
                                {{-- Initials rather than a stock silhouette, matching
                                     how a review with no photo is handled. --}}
                                <div class="flex aspect-[4/5] w-full items-center justify-center bg-brown/10">
                                    <span class="font-display text-5xl text-brown/45">{{ $member->initials }}</span>
                                </div>
                            @endif

                            <div class="p-6">
                                <h2 class="font-display text-xl">{{ $member->name }}</h2>

                                @if ($member->role)
                                    <p class="mt-1 text-[11px] uppercase tracking-[0.16em] text-brown/60">
                                        {{ $member->role }}
                                    </p>
                                @endif

                                @if ($member->bio)
                                    <p class="mt-4 text-sm leading-relaxed text-brown/80">{{ $member->bio }}</p>
                                @endif

                                @if ($member->email || $member->phone)
                                    <div class="mt-5 flex flex-wrap gap-x-5 gap-y-2 text-xs text-brown/70">
                                        @if ($member->email)
                                            <a href="mailto:{{ $member->email }}" class="transition-colors hover:text-dark-brown">
                                                {{ $member->email }}
                                            </a>
                                        @endif

                                        @if ($member->phone)
                                            <a href="tel:{{ preg_replace('/\s+/', '', $member->phone) }}"
                                               class="transition-colors hover:text-dark-brown">
                                                {{ $member->phone }}
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="bg-light-sand px-6 py-16 text-dark-brown sm:px-12 lg:px-20">
        <div class="mx-auto flex max-w-7xl flex-col items-start gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-display text-2xl lg:text-3xl">Travel with us</h2>
                <p class="mt-2 text-sm leading-relaxed text-brown/75">
                    These are the people who will plan your trip and meet you at the airport.
                </p>
            </div>

            <a href="{{ route('inquiry.create') }}"
               class="inline-flex items-center gap-2 rounded-full bg-dark-brown px-6 py-3.5 text-xs uppercase tracking-[0.14em] text-cream transition-colors hover:bg-brown">
                Plan a trip
                <x-ui-icon name="arrow" class="h-4 w-4" />
            </a>
        </div>
    </section>

@endsection
