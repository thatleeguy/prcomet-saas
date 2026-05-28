<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\OnePager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Spawns a blank, standalone one-pager (no match) for the given company
 * and redirects into the editor. POST-only so a back/refresh doesn't
 * create duplicates.
 *
 * Authorisation: company must belong to the user's current team. The
 * editor itself re-verifies, so this is the outer guard.
 */
class OnePagerCreateController extends Controller
{
    public function store(Request $request, Company $company): RedirectResponse
    {
        abort_unless($company->team_id === $request->user()->currentTeam?->id, 403);

        $page = OnePager::create([
            'company_id' => $company->id,
            'match_id' => null,
            'created_by_id' => $request->user()->id,
            'title' => null,
            'status' => OnePager::STATUS_DRAFT,
        ]);

        return redirect()->route('companies.onepagers.edit', [
            'company' => $company,
            'onePager' => $page,
        ]);
    }
}
