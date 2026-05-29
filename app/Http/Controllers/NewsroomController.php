<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\OnePager;

/**
 * Public newsroom page — a permanent home for the company's
 * published one-pagers that journalists can bookmark.
 *
 * URL: /newsroom/{slug}. 404s when the company doesn't exist or
 * has newsroom_published = false. No auth required; every render
 * is fully cacheable at the edge (we don't yet, but the
 * fingerprint-rendering avoids personalised content so it's
 * safe to put behind a CDN later).
 */
class NewsroomController extends Controller
{
    public function show(Company $company)
    {
        abort_unless((bool) $company->newsroom_published, 404);

        $onePagers = OnePager::query()
            ->where('company_id', $company->id)
            ->where('status', OnePager::STATUS_PUBLISHED)
            ->with(['match.publicationItem.source', 'match.author'])
            ->orderByDesc('published_at')
            ->get();

        return view('newsroom.show', [
            'company' => $company,
            'onePagers' => $onePagers,
        ]);
    }
}
