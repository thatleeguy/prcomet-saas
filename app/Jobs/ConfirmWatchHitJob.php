<?php

namespace App\Jobs;

use App\Models\WatchHit;
use App\Services\Llm\LlmClient;
use App\Services\Llm\Prompts\WatchHitConfirmationPrompt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Asks Claude whether the literal snippet that triggered a watch hit
 * actually refers to the watched entity, or whether it's a coincidental
 * string match.
 *
 * Failure mode is intentionally soft: any error leaves the hit with
 * confirmed_by_llm = null, which the UI renders as "pending review."
 * Literal hits are visible regardless of LLM state, so the user never
 * loses information to a Claude outage.
 */
class ConfirmWatchHitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(public readonly int $watchHitId) {}

    public function handle(LlmClient $llm): void
    {
        $hit = WatchHit::with('watch.company.team')->find($this->watchHitId);
        if (! $hit) {
            return;
        }

        // Re-check entitlement: the team may have been downgraded between
        // the literal scan and this job firing. Don't burn an LLM call on
        // a team that's no longer paying for it.
        if (! $hit->watch?->llmEnabled()) {
            return;
        }

        try {
            $result = WatchHitConfirmationPrompt::run($llm, $hit->watch, $hit);
        } catch (Throwable $e) {
            // Soft fail — leave the row pending for the next sweep.
            report($e);
            return;
        }

        if ($result === null) {
            return;
        }

        $hit->forceFill([
            'confirmed_by_llm' => $result['confirmed'],
            'llm_reasoning' => $result['reasoning'],
        ])->save();
    }
}
