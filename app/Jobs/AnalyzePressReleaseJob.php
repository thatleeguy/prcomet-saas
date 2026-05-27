<?php

namespace App\Jobs;

use App\Models\PressRelease;
use App\Services\Llm\LlmClient;
use App\Services\Llm\Prompts\PressReleaseAnalysisPrompt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Analyze a single PressRelease: extract entities (commodities, jurisdictions,
 * projects, people), topical tags, stance, and the headline-worthy key claims
 * an outreach pitch could anchor on.
 *
 * Dispatches {@see GenerateMatchesForReleaseJob} on success so Phase 6's match
 * engine picks it up automatically.
 */
class AnalyzePressReleaseJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $backoff = 30;

    public function __construct(public int $pressReleaseId) {}

    public function handle(LlmClient $llm): void
    {
        $release = PressRelease::find($this->pressReleaseId);

        if (! $release) {
            return;
        }

        if ($release->analysis_status === PressRelease::ANALYSIS_DONE) {
            return; // idempotent
        }

        $release->forceFill(['analysis_status' => PressRelease::ANALYSIS_RUNNING])->save();

        try {
            $result = PressReleaseAnalysisPrompt::run($llm, $release);
        } catch (\Throwable $e) {
            $release->forceFill(['analysis_status' => PressRelease::ANALYSIS_FAILED])->save();
            Log::warning('Press release analysis failed', [
                'press_release_id' => $release->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        $release->forceFill([
            'analysis_status' => PressRelease::ANALYSIS_DONE,
            'analyzed_at' => now(),
            'extracted_entities' => $result['entities'],
            'extracted_topics' => $result['topics'],
            'stance' => $result['stance'],
            'key_claims' => $result['key_claims'],
        ])->save();

        // Trigger match generation. Imported lazily so the class can be missing
        // during Phase 5 standalone testing without breaking imports.
        if (class_exists(GenerateMatchesForReleaseJob::class)) {
            GenerateMatchesForReleaseJob::dispatch($release->id);
        }
    }
}
