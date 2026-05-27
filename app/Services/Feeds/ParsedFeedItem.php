<?php

namespace App\Services\Feeds;

use Carbon\CarbonImmutable;

/**
 * One item parsed from an RSS or Atom feed.
 *
 * `guid` is the feed's stable identifier — defaults to the link if no <guid>
 * was present. We dedupe on (company, guid) so the upstream parser must always
 * resolve something stable here.
 */
final readonly class ParsedFeedItem
{
    public function __construct(
        public string $guid,
        public string $url,
        public string $title,
        public ?string $bodyHtml,
        public ?string $bodyText,
        public ?CarbonImmutable $publishedAt,
    ) {}
}
