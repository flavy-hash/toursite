@extends('layouts.app')

@section('title', 'Book Your Adventure')
@section('description', 'Tell us what you have in mind and we will come back with availability and a quote.')

{{-- An enquiry form has nothing to rank for, and the confirmation
     state should never be indexed. --}}
@section('robots', 'noindex, follow')

@section('content')

    <section class="relative isolate overflow-hidden bg-dark-brown px-6 pb-16 pt-32 sm:px-12 lg:px-20 lg:pb-20 lg:pt-44">
        <img
            src="{{ config('site.page_headers.inquiry') }}"
            alt=""
            fetchpriority="high"
            class="absolute inset-0 -z-10 h-full w-full object-cover object-center"
        >
        <div class="page-wash absolute inset-0 -z-10"></div>

        <div class="relative mx-auto max-w-3xl text-center">
            <p class="text-xs uppercase tracking-[0.22em] text-sand/70">Start Planning</p>
            <h1 class="mt-3 font-display text-4xl leading-tight text-white sm:text-5xl">Book your adventure</h1>
            <p class="mx-auto mt-4 max-w-xl text-base font-light leading-relaxed text-white/70">
                Tell us roughly what you want and when. We reply within one working day with availability, a quote and any suggestions.
            </p>
        </div>
    </section>

    <section class="bg-light-sand px-6 py-16 text-dark-brown sm:px-12 lg:px-20 lg:py-20">
        <div class="mx-auto max-w-3xl">

            {{-- Confirmation replaces the form after a successful submit. --}}
            @if (session('inquiry_sent'))
                <div class="rounded-3xl border border-brown/15 bg-cream p-8 text-center" role="status">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brown text-cream">
                        <x-ui-icon name="mail" class="h-6 w-6" />
                    </span>

                    <h2 class="mt-5 font-display text-3xl">Enquiry received</h2>
                    <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-brown/75">
                        Thank you — we have your enquiry about <strong>{{ session('inquiry_sent') }}</strong>
                        and will reply within one working day.
                    </p>

                    <div class="mt-7 flex flex-wrap justify-center gap-3">
                        <a href="/" class="rounded-full bg-brown px-6 py-3 text-xs uppercase tracking-[0.14em] text-cream transition-colors hover:bg-dark-brown">
                            Back to Home
                        </a>
                        <a href="{{ route('inquiry.create') }}" class="rounded-full border border-brown/30 px-6 py-3 text-xs uppercase tracking-[0.14em] transition-colors hover:bg-brown/10">
                            Send Another
                        </a>
                    </div>
                </div>
            @else

                @if ($errors->any())
                    <div class="mb-8 rounded-2xl border border-red-800/25 bg-red-900/5 p-5" role="alert">
                        <p class="text-sm font-semibold text-red-900">Please check the following:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-900/85">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('inquiry.store') }}" class="rounded-3xl border border-brown/12 bg-cream p-7 sm:p-9">
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">

                        <div class="sm:col-span-2">
                            <label for="tour_slug" class="mb-2 block text-[11px] uppercase tracking-[0.14em] text-brown/60">
                                Which trip?
                            </label>
                            <select id="tour_slug" name="tour_slug" class="form-field">
                                <option value="">Not sure yet — help me choose</option>
                                @foreach ($tours as $tour)
                                    <option value="{{ $tour->slug }}" @selected(old('tour_slug', $selected) === $tour->slug)>
                                        {{ $tour->name }} · {{ $tour->days }} · from {{ $tour->price }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="name" class="mb-2 block text-[11px] uppercase tracking-[0.14em] text-brown/60">
                                Your name <span class="text-red-800">*</span>
                            </label>
                            <input id="name" name="name" type="text" required autocomplete="name"
                                   value="{{ old('name') }}" class="form-field">
                        </div>

                        <div>
                            <label for="email" class="mb-2 block text-[11px] uppercase tracking-[0.14em] text-brown/60">
                                Email <span class="text-red-800">*</span>
                            </label>
                            <input id="email" name="email" type="email" required autocomplete="email"
                                   value="{{ old('email') }}" class="form-field">
                        </div>

                        <div>
                            <label for="phone" class="mb-2 block text-[11px] uppercase tracking-[0.14em] text-brown/60">
                                Phone or WhatsApp
                            </label>
                            <input id="phone" name="phone" type="tel" autocomplete="tel"
                                   value="{{ old('phone') }}" class="form-field">
                        </div>

                        <div>
                            <label for="travellers" class="mb-2 block text-[11px] uppercase tracking-[0.14em] text-brown/60">
                                Travellers <span class="text-red-800">*</span>
                            </label>
                            <input id="travellers" name="travellers" type="number" min="1" max="40" required
                                   value="{{ old('travellers', 2) }}" class="form-field">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="travel_date" class="mb-2 block text-[11px] uppercase tracking-[0.14em] text-brown/60">
                                Rough departure date
                            </label>
                            <input id="travel_date" name="travel_date" type="date"
                                   min="{{ now()->addDay()->toDateString() }}"
                                   value="{{ old('travel_date') }}" class="form-field">
                            <p class="mt-2 text-xs text-brown/55">An approximate month is fine — we can move it.</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="message" class="mb-2 block text-[11px] uppercase tracking-[0.14em] text-brown/60">
                                Anything we should know?
                            </label>
                            <textarea id="message" name="message" rows="5" maxlength="2000"
                                      placeholder="Dietary needs, mobility, whether you want to add Zanzibar, celebrating something…"
                                      class="form-field">{{ old('message') }}</textarea>
                        </div>
                    </div>

                    <button type="submit" class="mt-7 flex w-full items-center justify-center gap-2 rounded-full bg-brown px-8 py-4 text-xs uppercase tracking-[0.14em] text-cream transition-colors hover:bg-dark-brown">
                        Send Enquiry
                        <x-ui-icon name="arrow" class="h-4 w-4" />
                    </button>

                    <p class="mt-4 text-center text-xs text-brown/55">
                        No payment is taken now. We confirm availability before anything is booked.
                    </p>
                </form>
            @endif
        </div>
    </section>

@endsection
