@extends('layouts.app')

@section('title', 'Tanzania Safaris, Kilimanjaro Treks & Zanzibar Holidays')
@section('description', config('site.brand.tagline') . ' — privately guided safaris in the Serengeti and Ngorongoro, Kilimanjaro climbs and Zanzibar beach escapes, run from Arusha.')

@push('schema')
    @php
        $website = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('seo.organisation.name'),
            'url' => url('/'),
        ];
    @endphp
    <script type="application/ld+json">
    {!! json_encode($website, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
    @include('partials.hero')
    @include('partials.destinations')
    @include('partials.tours')
    @include('partials.why-us')
    @include('partials.stories')
    @include('partials.awards')
    @include('partials.newsletter')
    @include('partials.social')
@endsection
