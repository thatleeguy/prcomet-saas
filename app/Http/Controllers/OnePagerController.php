<?php

namespace App\Http\Controllers;

use App\Models\OnePager;
use App\Models\OnePagerView;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Public-facing one-pager renderer + view tracker.
 *
 * URL pattern is intentionally simple (`/onepagers/{uuid}`) so it pastes
 * cleanly into emails. View tracking happens server-side on render — we
 * don't trust a client beacon since journalists often preview via link-
 * unfurling proxies that don't run JS.
 */
class OnePagerController extends Controller
{
    public function show(string $uuid, Request $request)
    {
        $onePager = OnePager::with([
            'company',
            'match.pressRelease',
            'assets' => fn ($q) => $q->where('media_assets.is_active', true)->orderBy('one_pager_assets.sort_order'),
        ])->where('uuid', $uuid)->firstOrFail();

        if ($onePager->status !== OnePager::STATUS_PUBLISHED) {
            abort(404);
        }

        $this->recordView($onePager, $request);

        return view('onepagers.show', ['onePager' => $onePager]);
    }

    private function recordView(OnePager $onePager, Request $request): void
    {
        $userAgent = (string) $request->userAgent();

        if ($this->looksLikeBot($userAgent)) {
            return;
        }

        $ipHash = hash('sha256', $request->ip().config('app.key'));

        // Unique-view check BEFORE inserting the new row.
        $isUnique = ! OnePagerView::query()
            ->where('one_pager_id', $onePager->id)
            ->where('ip_hash', $ipHash)
            ->exists();

        OnePagerView::create([
            'one_pager_id' => $onePager->id,
            'ip_hash' => $ipHash,
            'user_agent' => mb_substr($userAgent, 0, 255),
            'referrer' => $request->headers->get('referer'),
            'viewed_at' => Carbon::now(),
        ]);

        $onePager->forceFill([
            'view_count' => $onePager->view_count + 1,
            'unique_view_count' => $isUnique ? $onePager->unique_view_count + 1 : $onePager->unique_view_count,
            'last_viewed_at' => now(),
        ])->save();
    }

    private function looksLikeBot(string $userAgent): bool
    {
        if ($userAgent === '') {
            return true;
        }
        $patterns = ['/bot/i', '/crawler/i', '/spider/i', '/preview/i', '/slack/i', '/telegram/i', '/whatsapp/i', '/facebookexternalhit/i', '/linkedinbot/i', '/twitterbot/i'];
        foreach ($patterns as $p) {
            if (preg_match($p, $userAgent)) {
                return true;
            }
        }
        return false;
    }
}
