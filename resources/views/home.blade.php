@extends('layouts.app')

@section('title', config('site.brand.name') . ' ' . config('site.brand.suffix') . ' — ' . config('site.brand.tagline'))

@section('content')
    @include('partials.hero')
    @include('partials.destinations')
    @include('partials.tours')
    @include('partials.why-us')
    @include('partials.stories')
    @include('partials.newsletter')
@endsection
