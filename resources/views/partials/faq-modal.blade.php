{{--
    "Ask a question" form, in a native <dialog>.

    Reuses the review modal's styles rather than duplicating three hundred
    lines of CSS for an identical treatment — the two dialogs are meant to look
    the same.

    <dialog> gives focus trapping, Esc to close, inert background content and
    the top layer from the browser, so none of that is reimplemented here.
--}}
<dialog
    id="faq-dialog"
    class="review-modal"
    aria-labelledby="faq-dialog-title"
    @if ($errors->faq->any()) data-open-on-load @endif
>
    <form action="{{ route('faq.ask') }}" method="POST">
        @csrf

        <header class="review-modal-header">
            <span class="review-modal-badge" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="h-5 w-5">
                    <path d="M9.1 9a3 3 0 1 1 4.2 2.7c-.8.4-1.3 1.1-1.3 2v.4"/>
                    <path d="M12 17.5h.01"/>
                </svg>
            </span>

            <div class="min-w-0">
                <h2 id="faq-dialog-title" class="font-display text-2xl text-cream">Ask a question</h2>
                <p class="mt-1 text-sm text-white/60">
                    We read every one. If it is useful to other travellers, we will add it to this page.
                </p>
            </div>

            <button type="button" class="review-modal-close" data-close-faq aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" class="h-5 w-5">
                    <path d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        </header>

        <div class="review-modal-body">

            @if ($errors->faq->any())
                <div class="mb-6 rounded-2xl border border-red-400/30 bg-red-500/10 p-4" role="alert">
                    <p class="text-sm font-semibold text-red-200">Please check the following:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-200/85">
                        @foreach ($errors->faq->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label for="fq-question" class="review-label">Your question <span class="req">*</span></label>
                <textarea id="fq-question" name="question" required rows="4" maxlength="255"
                          placeholder="e.g. How far in advance should I book a Kilimanjaro climb?"
                          class="review-field">{{ old('question', null, 'faq') }}</textarea>
            </div>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="fq-name" class="review-label">Your name</label>
                    <input id="fq-name" name="asked_by_name" type="text" maxlength="120"
                           placeholder="Optional" value="{{ old('asked_by_name', null, 'faq') }}"
                           class="review-field">
                </div>

                <div>
                    <label for="fq-email" class="review-label">Email</label>
                    <input id="fq-email" name="asked_by_email" type="email" maxlength="160"
                           placeholder="Optional — only if you want a reply"
                           value="{{ old('asked_by_email', null, 'faq') }}"
                           class="review-field">
                </div>
            </div>

            {{-- Honeypot. Hidden from people, irresistible to bots. --}}
            <div class="hidden" aria-hidden="true">
                <label for="fq-website">Website</label>
                <input id="fq-website" type="text" name="website" tabindex="-1" autocomplete="off">
            </div>

            <p class="mt-5 text-xs leading-relaxed text-white/50">
                Your name and email are never published — only the question and our answer are,
                and only if we think it helps others.
            </p>
        </div>

        <footer class="review-modal-footer">
            <button type="button" class="review-btn-ghost" data-close-faq>Cancel</button>
            <button type="submit" class="review-btn-primary">
                Send question
                <x-ui-icon name="arrow" class="h-4 w-4" />
            </button>
        </footer>
    </form>
</dialog>
