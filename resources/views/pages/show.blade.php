@extends('layouts.app')

@section('title', $page->metaTitle())
@section('description', $page->metaDescription())

@push('schema')
    <x-seo-breadcrumbs :trail="[['name' => $page->title, 'url' => $page->url()]]" />
@endpush

@section('content')

    @include('pages.partials.header', ['crumb' => [$page->title => null]])

    @include('pages.partials.sections')

    {{-- Closing prompt. Every page should offer somewhere to go next. --}}
    <section class="bg-light-sand px-6 py-16 text-dark-brown sm:px-12 lg:px-20">
        <div class="mx-auto flex max-w-7xl flex-col items-start gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-display text-2xl lg:text-3xl">Ready when you are</h2>
                <p class="mt-2 text-sm leading-relaxed text-brown/75">
                    Tell us roughly what you have in mind and we will put a route together.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('about.team') }}"
                   class="inline-flex items-center gap-2 rounded-full border border-brown/25 px-6 py-3.5 text-xs uppercase tracking-[0.14em] transition-colors hover:bg-brown/10">
                    Meet the team
                </a>

                <a href="{{ route('inquiry.create') }}"
                   class="inline-flex items-center gap-2 rounded-full bg-dark-brown px-6 py-3.5 text-xs uppercase tracking-[0.14em] text-cream transition-colors hover:bg-brown">
                    Plan a trip
                    <x-ui-icon name="arrow" class="h-4 w-4" />
                </a>
            </div>
        </div>
    </section>

@endsection
