<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block access to the workspace until a super-admin has activated the user's team.
 *
 * Customers sign up freely, but the workspace is gated behind explicit activation
 * (manual invoicing pre-PMF). Until then they land on the pending-activation page.
 *
 * Super-admins always pass — they can use the app even on inactive teams to debug.
 * Authentication-related routes (logout, profile, the pending page itself, the
 * Filament admin panel, Livewire endpoints) are exempt.
 */
class EnsureTeamActive
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Not authenticated — defer to the auth middleware to handle.
        if (! $user) {
            return $next($request);
        }

        // Super-admins bypass the gate entirely.
        if ($user->is_admin) {
            return $next($request);
        }

        $team = $user->currentTeam;

        if ($team && $team->is_active) {
            return $next($request);
        }

        return redirect()->route('teams.pending');
    }
}
