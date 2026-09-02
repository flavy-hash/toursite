<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInquiryRequest;
use App\Models\Inquiry;
use App\Models\Tour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InquiryController extends Controller
{
    /**
     * Show the booking enquiry form, pre-selecting a package when one is named
     * in the query string (that is how "Book This Adventure" arrives here).
     */
    public function create(Request $request): View
    {
        // query() can hand back an array (?tour[]=x), so insist on a string.
        $slug = $request->query('tour');
        $slug = is_string($slug) ? $slug : null;

        $tours = Tour::published()->ordered()->get();

        return view('inquiry.create', [
            'tours' => $tours,
            'selected' => $slug && $tours->contains('slug', $slug) ? $slug : null,
        ]);
    }

    public function store(StoreInquiryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Denormalise the name so the enquiry stays readable if the package
        // is later renamed.
        $slug = $data['tour_slug'] ?? null;
        $data['tour_name'] = $slug ? Tour::where('slug', $slug)->value('name') : null;

        $inquiry = Inquiry::create($data);

        return redirect()
            ->route('inquiry.create')
            ->with('inquiry_sent', $inquiry->tour_name ?: 'your trip');
    }
}
