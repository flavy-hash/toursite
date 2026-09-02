<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriberRequest;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;

class SubscriberController extends Controller
{
    public function store(StoreSubscriberRequest $request): RedirectResponse
    {
        $email = mb_strtolower(trim($request->validated('email')));

        /*
         * updateOrCreate keeps the list clean when someone signs up twice, and
         * clears unsubscribed_at so a returning subscriber is reinstated
         * rather than silently staying off the list.
         */
        Subscriber::updateOrCreate(
            ['email' => $email],
            [
                'source' => 'newsletter',
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ],
        );

        /*
         * The response is identical whether or not the address was already on
         * the list — otherwise the form becomes a way to test which addresses
         * are subscribed.
         */
        return back()
            ->with('subscribed', true)
            ->withFragment('newsletter');
    }
}
