<?php

use App\Services\Feeds\FeedParser;
use Illuminate\Support\Facades\Http;

it('parses an RSS 2.0 feed', function () {
    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>Aurelian Mining</title>
    <item>
      <title>Drill results: 12.4 g/t Au over 28m</title>
      <link>https://aurelian.example/news/drill-results-1</link>
      <guid>aur-2026-05-20-001</guid>
      <pubDate>Wed, 20 May 2026 09:00:00 GMT</pubDate>
      <description><![CDATA[<p>Hole AUR-26-001 returned an intercept...</p>]]></description>
    </item>
    <item>
      <title>C$10M financing closed</title>
      <link>https://aurelian.example/news/financing</link>
      <pubDate>Mon, 12 May 2026 14:00:00 GMT</pubDate>
      <description>Aurelian has closed a non-brokered placement.</description>
    </item>
  </channel>
</rss>
XML;

    $items = (new FeedParser)->parse($xml);

    expect($items)->toHaveCount(2)
        ->and($items[0]->guid)->toBe('aur-2026-05-20-001')
        ->and($items[0]->title)->toBe('Drill results: 12.4 g/t Au over 28m')
        ->and($items[0]->url)->toBe('https://aurelian.example/news/drill-results-1')
        ->and($items[0]->bodyText)->toContain('Hole AUR-26-001')
        ->and($items[0]->publishedAt->toIso8601String())->toBe('2026-05-20T09:00:00+00:00')
        // GUID defaults to link when missing
        ->and($items[1]->guid)->toBe('https://aurelian.example/news/financing');
});

it('parses an Atom 1.0 feed', function () {
    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
  <title>Northern Gold</title>
  <entry>
    <id>urn:northern:gold-2026-05-18</id>
    <title>Northern Gold reports Q1 results</title>
    <link href="https://northerngold.example/q1" />
    <published>2026-05-18T13:00:00Z</published>
    <content type="html">&lt;p&gt;Net income of \$2.1M...&lt;/p&gt;</content>
  </entry>
</feed>
XML;

    $items = (new FeedParser)->parse($xml);

    expect($items)->toHaveCount(1)
        ->and($items[0]->guid)->toBe('urn:northern:gold-2026-05-18')
        ->and($items[0]->title)->toBe('Northern Gold reports Q1 results')
        ->and($items[0]->url)->toBe('https://northerngold.example/q1')
        ->and($items[0]->bodyText)->toContain('Net income of $2.1M');
});

it('throws on malformed feed XML', function () {
    (new FeedParser)->parse('<rss><channel><item><title>broken');
})->throws(RuntimeException::class);

it('fetches a feed via HTTP', function () {
    Http::fake([
        'https://example.com/feed.xml' => Http::response(
            '<?xml version="1.0"?><rss><channel><item><title>Hi</title><link>https://x/1</link><guid>g1</guid></item></channel></rss>',
            200,
            ['Content-Type' => 'application/rss+xml']
        ),
    ]);

    $items = (new FeedParser)->fetch('https://example.com/feed.xml');

    expect($items)->toHaveCount(1)
        ->and($items[0]->title)->toBe('Hi');
});
