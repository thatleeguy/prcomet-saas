<?php

namespace App\Jobs;

use App\Models\PublicationItem;
use App\Models\Source;
use App\Services\Feeds\FeedParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Poll a single Source and upsert new PublicationItems.
 *
 * Mirrors {@see IngestCompanyRssJob} but for the corpus side. Currently only
 * the `rss` (and `atom`, same parser) strategies are implemented; podcast/X/
 * youtube are stubbed and skip silently so the rest of the pipeline can run.
 */
class IngestSourceJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public int $sourceId) {}

    public function handle(FeedParser $parser): void
    {
        $source = Source::find($this->sourceId);

        if (! $source || ! $source->is_active) {
            return;
        }

        if (! in_array($source->ingest_strategy, ['rss', 'atom'], true)) {
            // Non-feed strategies (api/manual/etc) are not implemented in v0.
            // The scheduler still picks them up so we can swap implementations
            // without changing the schedule.
            return;
        }

        if (! $source->feed_url) {
            return;
        }

        try {
            $items = $parser->fetch($source->feed_url);
        } catch (\Throwable $e) {
            Log::warning('Source ingest failed', [
                'source_id' => $source->id,
                'url' => $source->feed_url,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        $newCount = 0;

        foreach ($items as $item) {
            if ($item->guid === '' || $item->title === '') {
                continue;
            }

            $publicationItem = PublicationItem::firstOrCreate(
                [
                    'source_id' => $source->id,
                    'external_guid' => $item->guid,
                ],
                [
                    'url' => $item->url,
                    'title' => $item->title,
                    'body_text' => $item->bodyText,
                    'published_at' => $item->publishedAt,
                    'analysis_status' => PublicationItem::ANALYSIS_PENDING,
                ]
            );

            if ($publicationItem->wasRecentlyCreated) {
                $newCount++;
                AnalyzePublicationItemJob::dispatch($publicationItem->id);
            }
        }

        $source->forceFill(['last_ingested_at' => now()])->save();

        Log::info('Source ingested', [
            'source_id' => $source->id,
            'total_items' => count($items),
            'new_items' => $newCount,
        ]);
    }
}
