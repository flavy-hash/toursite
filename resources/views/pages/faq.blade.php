@extends('layouts.app')

@section('title', $page->metaTitle())
@section('description', $page->metaDescription())

{{--
    FAQPage structured data. Google renders these as expandable questions
    directly in the results, which is most of the reason an FAQ page earns
    its keep. Built from the same records the page shows, so the two can
    never disagree.
--}}
@if ($groups->isNotEmpty())
    @push('schema')
        @php
            $faqSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $groups->flatten()->map(fn ($faq) => [
                    '@type' => 'Question',
                    'name' => $faq->question,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq->answer,
                    ],
                ])->values()->all(),
            ];
        @endphp
        <script type="application/ld+json">
        {!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endpush
@endif

@push('schema')
    <x-seo-breadcrumbs :trail="[['name' => $page->title, 'url' => $page->url()]]" />
@endpush

@section('content')

    @include('pages.partials.header', ['crumb' => [$page->title => null]])

    @include('pages.partials.sections')

    @if ($groups->isNotEmpty())
        <section class="bg-cream px-6 py-16 text-dark-brown sm:px-12 lg:px-20 lg:py-24">
            <div class="mx-auto max-w-4xl space-y-14">
                @foreach ($groups as $category => $faqs)
                    <div>
                        {{-- Only worth a heading once there is more than one group;
                             a single "General" title over everything says nothing. --}}
                        @if ($groups->count() > 1)
                            <h2 class="mb-6 font-display text-2xl lg:text-3xl">{{ $category }}</h2>
                        @endif

                        <div class="divide-y divide-brown/12 border-y border-brown/12">
                            @foreach ($faqs as $faq)
                                {{--
                                    Native <details>: it opens without JavaScript,
                                    is keyboard-operable and announced correctly by
                                    screen readers, all without a line of script.
                                --}}
                                <details class="faq group">
                                    <summary class="faq-q">
                                        <span>{{ $faq->question }}</span>

                                        <span class="faq-mark" aria-hidden="true">
                                            <x-ui-icon name="chevron" class="h-4 w-4" />
                                        </span>
                                    </summary>

                                    <div class="faq-a">
                                        @foreach (preg_split('/\R{2,}/', trim($faq->answer)) as $paragraph)
                                            <p>{{ $paragraph }}</p>
                                        @endforeach
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section id="ask" class="bg-light-sand px-6 py-16 text-dark-brown sm:px-12 lg:px-20">
        @if (session('question_submitted'))
            {{-- Replaces nothing and hides nothing: the form is still there for
                 a second question, which people often have. --}}
            <div class="mx-auto mb-10 max-w-7xl rounded-2xl border border-brown/20 bg-cream p-5" role="status">
                <p class="font-display text-lg">Thank you — your question is with us.</p>
                <p class="mt-1 text-sm leading-relaxed text-brown/75">
                    We read every one. If it is useful to other travellers you will see it appear on
                    this page with our answer.
                </p>
            </div>
        @endif

        <div class="mx-auto flex max-w-7xl flex-col items-start gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-display text-2xl lg:text-3xl">Still not sure?</h2>
                <p class="mt-2 text-sm leading-relaxed text-brown/75">
                    Ask us directly — a real person answers, usually the same day.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="https://wa.me/{{ config('site.contact.whatsapp') }}"
                   target="_blank"
                   rel="noopener"
                   class="inline-flex items-center gap-2 rounded-full border border-brown/25 px-6 py-3.5 text-xs uppercase tracking-[0.14em] transition-colors hover:bg-brown/10">
                    <x-ui-icon name="whatsapp" class="h-4 w-4" />
                    WhatsApp us
                </a>

                <button type="button" data-open-faq
                        class="inline-flex items-center gap-2 rounded-full bg-dark-brown px-6 py-3.5 text-xs uppercase tracking-[0.14em] text-cream transition-colors hover:bg-brown">
                    Ask a question
                    <x-ui-icon name="arrow" class="h-4 w-4" />
                </button>
            </div>
        </div>
    </section>

    @include('partials.faq-modal')

@endsection
