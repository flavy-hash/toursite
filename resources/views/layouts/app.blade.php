<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include('partials.seo')

    <link rel="preload" as="font" type="font/woff" href="/assets/fonts/ChettaVissto.woff" crossorigin>
    <link rel="preload" as="font" type="font/ttf" href="/assets/fonts/Outfit-Regular.ttf" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <a href="#main" class="skip-link">Skip to content</a>

    @include('partials.header')

    <main id="main">
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.bottom-nav')

    <x-lightbox />
</body>
</html>
