@extends('layouts.app')

@section('title', 'Unsubscribed')
@section('robots', 'noindex, nofollow')

@section('content')
    <section class="flex min-h-[70svh] items-center bg-light-sand px-6 py-24 text-dark-brown sm:px-12 lg:px-20">
        <div class="mx-auto max-w-lg text-center">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-brown text-cream">
                <x-ui-icon name="mail" class="h-7 w-7" />
            </span>

            <h1 class="mt-6 font-display text-3xl lg:text-4xl">
                {{ $alreadyOut ? 'Already unsubscribed' : 'You&rsquo;re unsubscribed' }}
            </h1>

            <p class="mx-auto mt-4 max-w-md text-sm leading-relaxed text-brown/75">
                {{ $alreadyOut
                    ? 'That address was already off the list, so nothing has changed.'
                    : 'We will not send any more newsletters to that address. No hard feelings.' }}
            </p>

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="/" class="rounded-full bg-brown px-6 py-3 text-xs uppercase tracking-[0.14em] text-cream transition-colors hover:bg-dark-brown">
                    Back to Home
                </a>
                <a href="{{ route('tours.index') }}" class="rounded-full border border-brown/30 px-6 py-3 text-xs uppercase tracking-[0.14em] transition-colors hover:bg-brown/10">
                    Browse Tours
                </a>
            </div>
        </div>
    </section>
@endsection
