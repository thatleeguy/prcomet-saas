<?php

namespace App\Jobs;

use App\Jobs\AnalyzePressReleaseJob;
use App\Models\Company;
use App\Models\PressRelease;
use App\Services\Feeds\FeedParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Poll a single company's RSS feed and upsert new press releases.
 *
 * Dedupe is enforced at the DB level (unique on company_id+external_guid),
 * so re-running is safe and idempotent. New items dispatch
 * AnalyzePressReleaseJob (Phase 5) for downstream LLM processing.
 *
 * Scheduled hourly per active company in routes/console.php.
 */
class IngestCompanyRssJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public int $companyId) {}

    public function handle(FeedParser $parser): void
    {
        $company = Company::find($this->companyId);

        if (! $company || ! $company->is_active || ! $company->rss_feed_url) {
            return;
        }

        try {
            $items = $parser->fetch($company->rss_feed_url);
        } catch (\Throwable $e) {
            Log::warning('Feed ingest failed', [
                'company_id' => $company->id,
                'url' => $company->rss_feed_url,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        $newCount = 0;

        foreach ($items as $item) {
            // Skip items missing the basics — usually malformed feed entries.
            if ($item->guid === '' || $item->title === '') {
                continue;
            }

            $release = PressRelease::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'external_guid' => $item->guid,
                ],
                [
                    'source_url' => $item->url,
                    'title' => $item->title,
                    'body_html' => $item->bodyHtml,
                    'body_text' => $item->bodyText,
                    'published_at' => $item->publishedAt,
                    'analysis_status' => PressRelease::ANALYSIS_PENDING,
                ]
            );

            if ($release->wasRecentlyCreated) {
                $newCount++;
                AnalyzePressReleaseJob::dispatch($release->id);
            }
        }

        $company->forceFill(['last_ingested_at' => now()])->save();

        Log::info('Feed ingested', [
            'company_id' => $company->id,
            'total_items' => count($items),
            'new_items' => $newCount,
        ]);
    }
}
