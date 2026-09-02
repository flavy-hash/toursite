{{--
    Review form, in a native <dialog>.

    Using <dialog> rather than a hand-rolled overlay gets focus trapping, Esc to
    close, inert background content and the top layer from the browser, so none
    of that has to be reimplemented in JavaScript.
--}}
<dialog
    id="review-dialog"
    class="review-modal"
    aria-labelledby="review-dialog-title"
    @if ($errors->review->any()) data-open-on-load @endif
>
    <form action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data" data-review-form>
        @csrf

        <header class="review-modal-header">
            <span class="review-modal-badge" aria-hidden="true">
                <svg viewBox="0 0 24 24" class="h-5 w-5 fill-current">
                    <path d="M12 2.6l2.9 5.9 6.5.9-4.7 4.6 1.1 6.4-5.8-3-5.8 3 1.1-6.4L2.6 9.4l6.5-.9L12 2.6Z"/>
                </svg>
            </span>

            <div class="min-w-0">
                <h2 id="review-dialog-title" class="font-display text-2xl text-cream">Write a review</h2>
                <p class="mt-1 text-sm text-white/60">
                    Upload a photo (optional), rate your trip, and share your experience.
                </p>
            </div>

            <button type="button" class="review-modal-close" data-close-review aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" class="h-5 w-5">
                    <path d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        </header>

        <div class="review-modal-body">

            @if ($errors->review->any())
                <div class="mb-6 rounded-2xl border border-red-400/30 bg-red-500/10 p-4" role="alert">
                    <p class="text-sm font-semibold text-red-200">Please check the following:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-200/85">
                        @foreach ($errors->review->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="rv-name" class="review-label">Your name <span class="req">*</span></label>
                    <input id="rv-name" name="name" type="text" required maxlength="120"
                           placeholder="Full name" value="{{ old('name') }}" class="review-field">
                </div>

                <div>
                    <label for="rv-email" class="review-label">Email <span class="req">*</span></label>
                    <input id="rv-email" name="email" type="email" required
                           placeholder="you@example.com" value="{{ old('email') }}" class="review-field">
                    <p class="review-hint">Never published.</p>
                </div>

                <div>
                    <label for="rv-tour" class="review-label">Trip you took</label>
                    <select id="rv-tour" name="tour_slug" class="review-field">
                        <option value="">&mdash; No specific trip &mdash;</option>
                        @foreach ($tours as $tour)
                            <option value="{{ $tour->slug }}" @selected(old('tour_slug') === $tour->slug)>
                                {{ $tour->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <span class="review-label">Photo (optional)</span>

                    {{-- The input is visually hidden but still focusable, so the
                         dropzone stays keyboard reachable. --}}
                    <label for="rv-photo" class="review-dropzone">
                        <input id="rv-photo" name="photo" type="file"
                               accept="image/jpeg,image/png,image/webp" data-review-photo>
                        <span data-photo-label>
                            <strong>Click to upload</strong> &mdash; JPG / PNG / WEBP up to 2 MB
                        </span>
                    </label>
                </div>

                <div class="sm:col-span-2">
                    <span class="review-label">Overall rating <span class="req">*</span></span>

                    {{-- Radios rendered 5 to 1 so CSS can light every star below
                         the chosen one; works with no JavaScript at all. --}}
                    <div class="star-input" role="radiogroup" aria-label="Overall rating">
                        @for ($i = 5; $i >= 1; $i--)
                            <input
                                id="rv-star-{{ $i }}"
                                type="radio"
                                name="rating"
                                value="{{ $i }}"
                                required
                                @checked((int) old('rating') === $i)
                            >
                            <label for="rv-star-{{ $i }}" title="{{ $i }} out of 5">
                                <span class="sr-only">{{ $i }} {{ Str::plural('star', $i) }}</span>
                                <svg viewBox="0 0 24 24" class="h-8 w-8 fill-current" aria-hidden="true">
                                    <path d="M12 2.6l2.9 5.9 6.5.9-4.7 4.6 1.1 6.4-5.8-3-5.8 3 1.1-6.4L2.6 9.4l6.5-.9L12 2.6Z"/>
                                </svg>
                            </label>
                        @endfor
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label for="rv-title" class="review-label">Review headline</label>
                    <input id="rv-title" name="title" type="text" maxlength="140"
                           placeholder="e.g. An unforgettable week in the Serengeti"
                           value="{{ old('title') }}" class="review-field">
                </div>

                <div class="sm:col-span-2">
                    <label for="rv-body" class="review-label">Your review <span class="req">*</span></label>
                    <textarea id="rv-body" name="body" rows="5" required minlength="20" maxlength="2000"
                              placeholder="Tell future travellers what made your trip special…"
                              class="review-field">{{ old('body') }}</textarea>
                </div>

                <div class="sm:col-span-2 grid gap-5 sm:grid-cols-3">
                    <div>
                        <label for="rv-travelled" class="review-label">When you travelled</label>
                        <input id="rv-travelled" name="travelled_on" type="date" max="{{ now()->toDateString() }}"
                               value="{{ old('travelled_on') }}" class="review-field">
                    </div>

                    <div>
                        <label for="rv-guiding" class="review-label">Guiding</label>
                        <select id="rv-guiding" name="rating_guiding" class="review-field">
                            <option value="">Not rated</option>
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" @selected((int) old('rating_guiding') === $i)>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label for="rv-value" class="review-label">Value</label>
                        <select id="rv-value" name="rating_value" class="review-field">
                            <option value="">Not rated</option>
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" @selected((int) old('rating_value') === $i)>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- Honeypot --}}
                <div class="hidden" aria-hidden="true">
                    <label for="rv-website">Website</label>
                    <input id="rv-website" type="text" name="website" tabindex="-1" autocomplete="off">
                </div>
            </div>
        </div>

        <footer class="review-modal-footer">
            <button type="button" class="review-btn-ghost" data-close-review>Cancel</button>

            <button type="submit" class="review-btn-primary">
                Submit review
                <x-ui-icon name="arrow" class="h-4 w-4" />
            </button>
        </footer>
    </form>
</dialog>
