<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Posts a `current_company_id` change for the signed-in user.
 *
 * Mirrors Jetstream's CurrentTeamController pattern: small, side-effecting,
 * redirects back. The actual scope guard lives in User::switchCompany() —
 * a company that doesn't belong to the user's current team is refused.
 */
class CurrentCompanyController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'integer'],
        ]);

        $user = $request->user();

        // Scope the lookup itself by current team — we don't want to confirm
        // "company X exists" to a user who has no business knowing about it.
        // Falling through to 404 here is the same response a user gets for
        // a truly-nonexistent id, so existence isn't leaked by timing either.
        $company = Company::where('team_id', $user->currentTeam?->id)
            ->findOrFail($data['company_id']);

        $user->switchCompany($company);

        // Land the user on the company's matches list — that's the page
        // they usually want after switching focus.
        return redirect()->route('companies.matches', $company);
    }
}
