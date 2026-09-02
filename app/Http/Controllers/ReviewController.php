<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Review;
use App\Models\Tour;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        return view('reviews.index', [
            'reviews' => Review::published()->newestFirst()->paginate(9)->withQueryString(),
            'summary' => Review::summary(),
            'tours' => Tour::published()->ordered()->get(),
        ]);
    }

    public function store(StoreReviewRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('reviews', 'public');
        }

        $data['tour_name'] = ($data['tour_slug'] ?? null)
            ? Tour::where('slug', $data['tour_slug'])->value('name')
            : null;

        /*
         * Held for moderation. is_published stays false so nothing reaches the
         * public page until a person has read it.
         */
        unset($data['website']);
        Review::create($data);

        return redirect()
            ->route('reviews.index')
            ->with('review_submitted', true)
            ->withFragment('review-form');
    }
}
