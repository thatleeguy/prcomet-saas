<?php

namespace App\Services\Feeds;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use SimpleXMLElement;

/**
 * Minimal RSS 2.0 + Atom 1.0 feed parser. Sufficient for IR press-release feeds.
 *
 * Why not laminas-feed: one fewer dependency, no media/iTunes extensions needed
 * yet. We'll switch when podcast ingest (Phase 4) needs the iTunes namespace.
 *
 * HTTP is via Laravel's Http facade so tests can `Http::fake()` cleanly.
 */
class FeedParser
{
    public function __construct(
        private readonly int $timeoutSeconds = 15,
    ) {}

    /**
     * Fetch a feed URL and return its items. Throws on transport failure or
     * malformed XML — callers (jobs) should catch and log.
     *
     * @return array<int, ParsedFeedItem>
     */
    public function fetch(string $url): array
    {
        $response = Http::timeout($this->timeoutSeconds)
            ->withUserAgent('PrComet/0.1 (+https://prcomet.com/bot)')
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException("Feed fetch failed for {$url}: HTTP {$response->status()}");
        }

        return $this->parse($response->body());
    }

    /**
     * @return array<int, ParsedFeedItem>
     */
    public function parse(string $xml): array
    {
        $previousState = libxml_use_internal_errors(true);

        try {
            $root = new SimpleXMLElement($xml, LIBXML_NOCDATA | LIBXML_NONET);
        } catch (\Throwable $e) {
            throw new RuntimeException('Malformed feed XML: '.$e->getMessage(), previous: $e);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousState);
        }

        return $this->isAtom($root) ? $this->parseAtom($root) : $this->parseRss($root);
    }

    private function isAtom(SimpleXMLElement $root): bool
    {
        // <feed xmlns="http://www.w3.org/2005/Atom"> root → Atom.
        return $root->getName() === 'feed';
    }

    /** @return array<int, ParsedFeedItem> */
    private function parseRss(SimpleXMLElement $root): array
    {
        $items = [];

        // RSS 2.0: <rss><channel><item>...</item></channel></rss>
        $channel = $root->channel ?? $root;

        foreach ($channel->item as $item) {
            $link = trim((string) $item->link);
            $guid = trim((string) $item->guid) ?: $link;
            $title = trim((string) $item->title);
            $description = (string) $item->description;
            $contentEncoded = (string) ($item->children('content', true)->encoded ?? '');
            $pubDate = trim((string) $item->pubDate);

            $bodyHtml = $contentEncoded ?: $description;

            $items[] = new ParsedFeedItem(
                guid: $guid,
                url: $link,
                title: $title,
                bodyHtml: $bodyHtml ?: null,
                bodyText: $bodyHtml ? $this->htmlToText($bodyHtml) : null,
                publishedAt: $this->parseDate($pubDate),
            );
        }

        return $items;
    }

    /** @return array<int, ParsedFeedItem> */
    private function parseAtom(SimpleXMLElement $root): array
    {
        $items = [];

        foreach ($root->entry as $entry) {
            $id = trim((string) $entry->id);
            $link = '';
            foreach ($entry->link as $linkEl) {
                $rel = (string) $linkEl['rel'];
                if ($rel === '' || $rel === 'alternate') {
                    $link = (string) $linkEl['href'];
                    break;
                }
            }

            $title = trim((string) $entry->title);
            $content = (string) $entry->content;
            $summary = (string) $entry->summary;
            $bodyHtml = $content ?: $summary;
            $updated = trim((string) ($entry->published ?? $entry->updated));

            $items[] = new ParsedFeedItem(
                guid: $id ?: $link,
                url: $link,
                title: $title,
                bodyHtml: $bodyHtml ?: null,
                bodyText: $bodyHtml ? $this->htmlToText($bodyHtml) : null,
                publishedAt: $this->parseDate($updated),
            );
        }

        return $items;
    }

    private function parseDate(string $raw): ?CarbonImmutable
    {
        if ($raw === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($raw);
        } catch (\Throwable) {
            return null;
        }
    }

    private function htmlToText(string $html): string
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Collapse whitespace runs but keep paragraph breaks.
        $text = preg_replace("/[ \t]+/", ' ', $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return trim($text);
    }
}
