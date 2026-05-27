<?php

namespace App\Jobs;

use App\Models\MatchRecord;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetch a placement URL the user just attached to a match and pull out
 * title + meta description + published date. Best-effort extraction from
 * common Open Graph / `<meta>` / `<title>` tags. Failures are logged and
 * silenced — placement title can stay null and the URL still works.
 */
class FetchPlacementMetadataJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 30;

    public function __construct(public int $matchId) {}

    public function handle(): void
    {
        $match = MatchRecord::find($this->matchId);

        if (! $match || ! $match->placement_url) {
            return;
        }

        try {
            $response = Http::timeout(15)
                ->withUserAgent('PrComet/0.1 (+https://prcomet.com/bot)')
                ->get($match->placement_url);

            if (! $response->successful()) {
                throw new \RuntimeException("HTTP {$response->status()}");
            }

            $html = $response->body();
        } catch (\Throwable $e) {
            Log::warning('Placement fetch failed', [
                'match_id' => $match->id,
                'url' => $match->placement_url,
                'error' => $e->getMessage(),
            ]);
            $match->forceFill(['placement_fetched_at' => now()])->save();
            return;
        }

        $match->forceFill([
            'placement_title' => $this->extractTitle($html),
            'placement_description' => $this->extractDescription($html),
            'placement_published_at' => $this->extractPublishedAt($html),
            'placement_fetched_at' => now(),
        ])->save();
    }

    private function extractTitle(string $html): ?string
    {
        if ($value = $this->metaContent($html, 'og:title')) {
            return $value;
        }
        if (preg_match('/<title>([^<]+)<\/title>/i', $html, $m)) {
            return trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5));
        }
        return null;
    }

    private function extractDescription(string $html): ?string
    {
        foreach (['og:description', 'twitter:description', 'description'] as $key) {
            if ($value = $this->metaContent($html, $key)) {
                return $value;
            }
        }
        return null;
    }

    private function extractPublishedAt(string $html): ?CarbonImmutable
    {
        foreach (['article:published_time', 'datePublished'] as $key) {
            if ($value = $this->metaContent($html, $key)) {
                try {
                    return CarbonImmutable::parse($value);
                } catch (\Throwable) {
                    continue;
                }
            }
        }
        return null;
    }

    /**
     * Pull a <meta> tag's content by property/name/itemprop, supporting either
     * quote style. Backreference-guarded so apostrophes inside double-quoted
     * content (and vice versa) survive the extraction.
     */
    private function metaContent(string $html, string $key): ?string
    {
        $quoted = preg_quote($key, '/');
        $pattern = '/<meta\s+(?:property|name|itemprop)=(["\'])'.$quoted.'\1\s+content=(["\'])((?:(?!\2).)+)\2/i';

        if (preg_match($pattern, $html, $m)) {
            return trim(html_entity_decode($m[3], ENT_QUOTES | ENT_HTML5));
        }
        return null;
    }
}
