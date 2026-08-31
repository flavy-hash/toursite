@extends('layouts.app')

@section('title', config('site.brand.name') . ' ' . config('site.brand.suffix') . ' — ' . config('site.brand.tagline'))

@section('content')
    @include('partials.hero')
@endsection
