<?php

namespace App\Jobs;

use App\Mail\MatchDigest;
use App\Models\Company;
use App\Models\MatchRecord;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

/**
 * Send a single user's digest of new matches.
 *
 * Skips if the user is off-cadence, has no team, the team is inactive, or
 * there are no new matches since the previous digest. Updates digest_sent_at
 * even on empty runs so the next tick checks against the right window.
 */
class SendMatchDigestsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $userId) {}

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (! $user || $user->digest_frequency === User::DIGEST_OFF) {
            return;
        }

        $team = $user->currentTeam;
        if (! $team || ! $team->is_active) {
            return;
        }

        $since = $user->digest_sent_at ?? match ($user->digest_frequency) {
            User::DIGEST_WEEKLY => now()->subDays(7),
            default => now()->subDay(),
        };

        $teamCompanyIds = Company::where('team_id', $team->id)->pluck('id');

        $matches = MatchRecord::query()
            ->with(['company', 'publicationItem.source', 'author'])
            ->whereIn('company_id', $teamCompanyIds)
            ->where('created_at', '>=', $since)
            ->orderByDesc('score')
            ->limit(10)
            ->get();

        // Mark the run regardless — empty digests still advance the window.
        $user->forceFill(['digest_sent_at' => now()])->save();

        if ($matches->isEmpty()) {
            return;
        }

        Mail::to($user->email)
            ->send(new MatchDigest($user, $matches, $user->digest_frequency));
    }
}
