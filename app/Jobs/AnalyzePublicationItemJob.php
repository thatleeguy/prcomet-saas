<?php

namespace App\Jobs;

use App\Models\Author;
use App\Models\ExtractedClaim;
use App\Models\PublicationItem;
use App\Services\Llm\LlmClient;
use App\Services\Llm\Prompts\PublicationItemAnalysisPrompt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Analyze a PublicationItem and persist:
 * - entities/topics/stance on the item itself
 * - the identified author (creating or matching on slug)
 * - claims with timeframes for downstream verification
 *
 * Author profile refresh is queued separately so it can be debounced —
 * one new article shouldn't kick a full re-summary on a hot author.
 */
class AnalyzePublicationItemJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $backoff = 30;

    public function __construct(public int $publicationItemId) {}

    public function handle(LlmClient $llm): void
    {
        $item = PublicationItem::find($this->publicationItemId);

        if (! $item) {
            return;
        }

        if ($item->analysis_status === PublicationItem::ANALYSIS_DONE) {
            return;
        }

        $item->forceFill(['analysis_status' => PublicationItem::ANALYSIS_RUNNING])->save();

        try {
            $result = PublicationItemAnalysisPrompt::run($llm, $item);
        } catch (\Throwable $e) {
            $item->forceFill(['analysis_status' => PublicationItem::ANALYSIS_FAILED])->save();
            Log::warning('Publication item analysis failed', [
                'publication_item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        $author = $this->resolveAuthor($item, $result['author'] ?? []);

        $item->forceFill([
            'author_id' => $author?->id,
            'analysis_status' => PublicationItem::ANALYSIS_DONE,
            'analyzed_at' => now(),
            'extracted_entities' => $result['entities'],
            'extracted_topics' => $result['topics'],
            'stance' => $result['stance'],
        ])->save();

        // Replace claims for this item — re-analysis should be idempotent.
        ExtractedClaim::where('publication_item_id', $item->id)->delete();
        foreach (($result['claims'] ?? []) as $claim) {
            if (! is_array($claim) || empty($claim['claim'])) {
                continue;
            }
            ExtractedClaim::create([
                'publication_item_id' => $item->id,
                'author_id' => $author?->id,
                'claim_text' => $claim['claim'],
                'topic' => $claim['topic'] ?? null,
                'stance' => $result['stance'],
                'predicted_outcome' => $claim['predicted_outcome'] ?? null,
                'timeframe' => $claim['timeframe'] ?? null,
            ]);
        }

        if ($author) {
            BuildAuthorProfileJob::dispatch($author->id);
        }
    }

    private function resolveAuthor(PublicationItem $item, array $authorData): ?Author
    {
        $name = is_string($authorData['name'] ?? null) ? trim($authorData['name']) : '';

        if ($name === '') {
            return null;
        }

        $slug = Str::slug($name);

        return Author::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'x_handle' => $authorData['x_handle'] ?? null,
                'primary_source_id' => $item->source_id,
            ]
        );
    }
}
