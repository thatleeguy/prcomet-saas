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
            'company_id' => ['required', 'integer', 'exists:companies,id'],
        ]);

        $user = $request->user();
        $company = Company::findOrFail($data['company_id']);

        if (! $user->switchCompany($company)) {
            abort(403, 'Company does not belong to your current team.');
        }

        // Land the user on the company's matches list — that's the page
        // they usually want after switching focus.
        return redirect()->route('companies.matches', $company);
    }
}
