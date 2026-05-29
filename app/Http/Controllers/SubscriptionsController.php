<?php

namespace App\Http\Controllers;

use App\Models\NewsroomSubscriber;

/**
 * Token-based subscriber lifecycle endpoints. Each route is reachable
 * only with the random 64-char token issued at subscribe time — no
 * password, no login. Substack pattern.
 */
class SubscriptionsController extends Controller
{
    /**
     * Confirm a brand-new subscription (double opt-in step). Idempotent.
     */
    public function confirm(string $token)
    {
        $subscriber = NewsroomSubscriber::where('token', $token)->firstOrFail();

        if (! $subscriber->isConfirmed()) {
            $subscriber->forceFill([
                'confirmed_at' => now(),
                'unsubscribed_at' => null,
            ])->save();
        }

        return view('newsroom.confirmed', [
            'subscriber' => $subscriber,
            'manageUrl' => $subscriber->manageUrl(),
        ]);
    }

    /**
     * One-click global unsubscribe. Sets the flag on the identity
     * itself — every active pivot stops dispatching. Re-subscribing
     * via any /newsroom/{slug} clears the flag automatically.
     */
    public function unsubscribeAll(string $token)
    {
        $subscriber = NewsroomSubscriber::where('token', $token)->firstOrFail();

        $subscriber->forceFill(['unsubscribed_at' => now()])->save();

        return view('newsroom.unsubscribed', [
            'subscriber' => $subscriber,
        ]);
    }
}
