<?php

namespace App\Jobs;

use App\Models\MatchRecord;
use App\Models\PressRelease;
use App\Services\Llm\LlmClient;
use App\Services\Llm\Prompts\MatchBriefPrompt;
use App\Services\Matching\MatchCandidateFinder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Phase 6 — the heart of the product.
 *
 * Given a freshly analyzed press release, find candidate publication items
 * via the cheap topic-overlap finder, then ask the LLM (Opus by default) to
 * rank the top picks and write briefs with rationale + suggested angle +
 * citations.
 *
 * Hard rule: matches below `match.min_confidence` are not persisted. Trust
 * beats coverage — a mediocre brief loses the customer on first use.
 */
class GenerateMatchesForReleaseJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $backoff = 60;

    public function __construct(public int $pressReleaseId) {}

    public function handle(LlmClient $llm, MatchCandidateFinder $finder): void
    {
        $release = PressRelease::with('company.team')->find($this->pressReleaseId);

        if (! $release || $release->analysis_status !== PressRelease::ANALYSIS_DONE) {
            return;
        }

        if (! $release->company || ! $release->company->team) {
            return;
        }

        $candidates = $finder->findFor($release, $release->company->team);

        if ($candidates->isEmpty()) {
            Log::info('No match candidates', ['press_release_id' => $release->id]);
            return;
        }

        try {
            $picks = MatchBriefPrompt::run($llm, $release, $candidates);
        } catch (\App\Services\Llm\BudgetExceededException $e) {
            // System LLM cap reached — skip brief generation. The
            // upstream candidate set is still useful for later retry
            // once the budget window rolls; just don't create matches
            // with hallucinated scores in the meantime.
            Log::info('Match brief skipped — LLM budget exceeded', [
                'press_release_id' => $release->id,
                'window' => $e->window,
            ]);
            return;
        }

        $minConfidence = (float) config('match.min_confidence', 0.5);

        foreach ($picks as $pick) {
            if ($pick['score'] < $minConfidence) {
                continue;
            }

            $publicationItem = $candidates->firstWhere('id', $pick['publication_item_id']);
            if (! $publicationItem) {
                continue;
            }

            MatchRecord::updateOrCreate(
                [
                    'press_release_id' => $release->id,
                    'publication_item_id' => $pick['publication_item_id'],
                ],
                [
                    'company_id' => $release->company_id,
                    'author_id' => $publicationItem->author_id,
                    'score' => $pick['score'],
                    'rationale_md' => $pick['rationale'],
                    'suggested_angle_md' => $pick['suggested_angle'],
                    'citations' => $pick['citations'],
                    'status' => MatchRecord::STATUS_NEW,
                ]
            );
        }
    }
}
