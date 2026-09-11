<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFaqQuestionRequest;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;

class FaqQuestionController extends Controller
{
    /**
     * Takes a question from the form on /faq into the moderation queue.
     *
     * It is stored as an ordinary FAQ row with no answer and no category,
     * unpublished — so staff answer it in the same screen they write their
     * own questions in, and publishing it is the same one click.
     */
    public function store(StoreFaqQuestionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Faq::create([
            'question' => $data['question'],
            'answer' => null,
            'category' => null,
            'asked_by_name' => $data['asked_by_name'] ?? null,
            'asked_by_email' => $data['asked_by_email'] ?? null,
            'asked_at' => now(),

            // Joins the end of the list, and stays off the page until answered.
            'sort_order' => (int) Faq::max('sort_order') + 1,
            'is_published' => false,
        ]);

        return redirect()
            ->route('faq')
            ->with('question_submitted', true)
            ->withFragment('ask');
    }
}
