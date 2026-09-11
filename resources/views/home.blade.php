@extends('layouts.app')

@section('title', 'Tanzania Safaris, Kilimanjaro Treks & Zanzibar Holidays')
@section('description', config('site.brand.tagline') . ' — privately guided safaris in the Serengeti and Ngorongoro, Kilimanjaro climbs and Zanzibar beach escapes, run from Arusha.')

@section('content')
    @include('partials.hero')
    @include('partials.destinations')
    @include('partials.tours')
    @include('partials.why-us')
    @include('partials.stories')
    @include('partials.awards')
    @include('partials.video')
    @include('partials.newsletter')
    @include('partials.social')
@endsection
