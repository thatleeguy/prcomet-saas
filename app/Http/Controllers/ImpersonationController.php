<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Lets a super-admin "become" another user (to debug what they see) and then
 * snap back to their original admin account.
 *
 * State lives in the session under `impersonator_id`, which is the original
 * admin's user ID. While that key is present, the layout shows a banner
 * with a "Return to admin" button posting to {@see stop}.
 */
class ImpersonationController extends Controller
{
    /**
     * Stop impersonating and switch back to the original admin account.
     * Safe to hit even if no impersonation is active — just bounces home.
     */
    public function stop(): RedirectResponse
    {
        $impersonatorId = session()->pull('impersonator_id');

        if (! $impersonatorId) {
            return redirect('/');
        }

        $admin = User::find($impersonatorId);

        if (! $admin) {
            // The original admin is gone for some reason — just sign out the
            // current session so the impersonated identity doesn't linger.
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect('/');
        }

        Auth::login($admin);

        return redirect('/manage');
    }
}
