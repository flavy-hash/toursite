<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriberRequest;
use App\Mail\SubscriberWelcome;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

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
        $subscriber = Subscriber::updateOrCreate(
            ['email' => $email],
            [
                'source' => 'newsletter',
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ],
        );

        /*
         * The sign-up is what matters, and it is already saved. A mail server
         * that is down, slow or unreachable must not turn a successful
         * subscription into a 500 for the visitor — so the failure is logged
         * and the welcome email is simply lost.
         */
        try {
            Mail::to($subscriber->email)->queue(new SubscriberWelcome($subscriber));
        } catch (Throwable $e) {
            Log::warning('Welcome email could not be sent.', [
                'subscriber' => $subscriber->id,
                'error' => $e->getMessage(),
            ]);
        }

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
