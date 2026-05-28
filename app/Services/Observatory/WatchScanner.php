<?php

namespace App\Services\Observatory;

use App\Jobs\ConfirmWatchHitJob;
use App\Models\PublicationItem;
use App\Models\Source;
use App\Models\Watch;
use App\Models\WatchHit;

/**
 * Runs a watch's literal-term search across the team's visible corpus.
 *
 * Scope is intentionally narrow: PublicationItems whose source is
 * visible to the watch's team (union of global and team-scoped
 * sources). Press releases are excluded — the user's own PR archive
 * isn't the kind of "outside world saying X" signal Observatory is
 * designed to surface.
 *
 * Matching is case-insensitive whole-word against `title` and
 * `body_text`. On match, we upsert a WatchHit (unique on watch +
 * content), grab a short snippet, and bump the watch's denormalised
 * counters.
 *
 * When the watch's effective mode is literal_llm (team is entitled +
 * the user chose that mode), each newly-created hit is queued for LLM
 * confirmation in a follow-up job.
 */
class WatchScanner
{
    private const SNIPPET_RADIUS = 160;

    /**
     * Scan the team's corpus for a single watch.
     * Returns the number of newly recorded hits.
     */
    public function scan(Watch $watch): int
    {
        $watch->loadMissing('company.team');

        if (! $watch->is_active) {
            return 0;
        }

        $team = $watch->company?->team;
        if (! $team) {
            return 0;
        }

        $terms = collect($watch->terms ?? [])
            ->map(fn ($t) => trim((string) $t))
            ->filter()
            ->values();

        if ($terms->isEmpty()) {
            return 0;
        }

        $created = $this->scanPublicationItems($watch, $team->id, $terms);

        if ($created > 0) {
            $watch->forceFill([
                'last_matched_at' => now(),
                'hit_count' => $watch->hits()->count(),
            ])->save();
        }

        return $created;
    }

    private function scanPublicationItems(Watch $watch, int $teamId, $terms): int
    {
        $sourceIds = Source::visibleTo(\App\Models\Team::find($teamId))->pluck('id');

        if ($sourceIds->isEmpty()) {
            return 0;
        }

        // Pull items in chunks so we don't load the entire corpus into memory.
        $created = 0;
        PublicationItem::query()
            ->whereIn('source_id', $sourceIds)
            ->select(['id', 'title', 'body_text'])
            ->chunkById(200, function ($items) use ($watch, $terms, &$created) {
                foreach ($items as $item) {
                    $created += $this->matchAndRecord(
                        $watch,
                        WatchHit::TYPE_PUBLICATION_ITEM,
                        $item->id,
                        (string) $item->title,
                        (string) $item->body_text,
                        $terms,
                    );
                }
            });

        return $created;
    }

    /**
     * Try every term against title + body, recording the first match found.
     * Returns 1 if a new hit row was created, 0 if no match or already seen.
     */
    private function matchAndRecord(Watch $watch, string $contentType, int $contentId, string $title, string $body, $terms): int
    {
        $haystack = $title."\n\n".$body;

        foreach ($terms as $term) {
            $pattern = '/\b'.preg_quote($term, '/').'\b/iu';
            if (preg_match($pattern, $haystack, $matches, PREG_OFFSET_CAPTURE) === 1) {
                $offset = $matches[0][1];
                $snippet = $this->extractSnippet($haystack, $offset, strlen($matches[0][0]));

                $hit = WatchHit::query()->firstOrCreate(
                    [
                        'watch_id' => $watch->id,
                        'content_type' => $contentType,
                        'content_id' => $contentId,
                    ],
                    [
                        'matched_term' => $term,
                        'context_snippet' => $snippet,
                        'matched_at' => now(),
                    ],
                );

                if ($hit->wasRecentlyCreated) {
                    if ($watch->llmEnabled()) {
                        ConfirmWatchHitJob::dispatch($hit->id);
                    }
                    return 1;
                }
                return 0;
            }
        }

        return 0;
    }

    /**
     * Extract ~320 chars of context centred on the match, with ellipses
     * when truncated. Multibyte-safe so we don't slice mid-character.
     */
    private function extractSnippet(string $text, int $byteOffset, int $matchLen): string
    {
        // Convert byte offset to a character index for mb_substr safety.
        $charOffset = mb_strlen(substr($text, 0, $byteOffset));
        $start = max(0, $charOffset - self::SNIPPET_RADIUS);
        $length = self::SNIPPET_RADIUS * 2 + mb_strlen(substr($text, $byteOffset, $matchLen));

        $snippet = mb_substr($text, $start, $length);
        if ($start > 0) {
            $snippet = '…'.$snippet;
        }
        if ($start + $length < mb_strlen($text)) {
            $snippet .= '…';
        }

        return $snippet;
    }
}
