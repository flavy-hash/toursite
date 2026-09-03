<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\View\View;

class UnsubscribeController extends Controller
{
    /**
     * Opt someone out. The route is signed, so the link cannot be guessed and
     * nobody can unsubscribe a stranger.
     */
    public function __invoke(Subscriber $subscriber): View
    {
        $alreadyOut = ! $subscriber->is_subscribed;

        if (! $alreadyOut) {
            $subscriber->unsubscribe();
        }

        return view('unsubscribed', [
            'subscriber' => $subscriber,
            'alreadyOut' => $alreadyOut,
        ]);
    }
}
