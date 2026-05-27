<?php

use App\Jobs\AnalyzePublicationItemJob;
use App\Jobs\IngestSourceJob;
use App\Models\PublicationItem;
use App\Models\Source;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

function fakeSourceFeed(array $items): string
{
    $entries = '';
    foreach ($items as $i) {
        $entries .= "<item><title>{$i['title']}</title><link>{$i['link']}</link><guid>{$i['guid']}</guid><pubDate>{$i['pub']}</pubDate><description>{$i['desc']}</description></item>";
    }
    return "<?xml version=\"1.0\"?><rss version=\"2.0\"><channel><title>F</title>{$entries}</channel></rss>";
}

beforeEach(fn () => Bus::fake([AnalyzePublicationItemJob::class]));

it('ingests publication items from an RSS source', function () {
    $source = Source::factory()->create(['feed_url' => 'https://pub.example/rss']);

    Http::fake([
        'pub.example/rss' => Http::response(fakeSourceFeed([
            ['title' => 'Gold thesis', 'link' => 'https://pub.example/1', 'guid' => 'p-1', 'pub' => 'Wed, 20 May 2026 10:00:00 GMT', 'desc' => 'Gold is going up'],
            ['title' => 'Lithium glut', 'link' => 'https://pub.example/2', 'guid' => 'p-2', 'pub' => 'Thu, 21 May 2026 10:00:00 GMT', 'desc' => 'Glut is overstated'],
        ])),
    ]);

    dispatch_sync(new IngestSourceJob($source->id));

    expect(PublicationItem::count())->toBe(2);
    Bus::assertDispatched(AnalyzePublicationItemJob::class, 2);
});

it('skips non-rss ingest strategies for now', function () {
    $source = Source::factory()->create([
        'feed_url' => 'https://pub.example/rss',
        'ingest_strategy' => 'api',
    ]);

    Http::fake(['*' => Http::response(fakeSourceFeed([
        ['title' => 'x', 'link' => 'https://pub.example/1', 'guid' => 'p-1', 'pub' => 'Wed, 20 May 2026 10:00:00 GMT', 'desc' => 'd'],
    ]))]);

    dispatch_sync(new IngestSourceJob($source->id));

    expect(PublicationItem::count())->toBe(0);
    Bus::assertNotDispatched(AnalyzePublicationItemJob::class);
});

it('skips inactive sources', function () {
    $source = Source::factory()->create([
        'feed_url' => 'https://pub.example/rss',
        'is_active' => false,
    ]);

    Http::fake(['*' => Http::response(fakeSourceFeed([
        ['title' => 'x', 'link' => 'https://pub.example/1', 'guid' => 'p-1', 'pub' => 'Wed, 20 May 2026 10:00:00 GMT', 'desc' => 'd'],
    ]))]);

    dispatch_sync(new IngestSourceJob($source->id));
    expect(PublicationItem::count())->toBe(0);
});

it('returns the union of global and team-scoped sources via visibleTo', function () {
    $teamA = \App\Models\Team::factory()->create();
    $teamB = \App\Models\Team::factory()->create();

    $global = Source::factory()->create();
    $forA = Source::factory()->team($teamA)->create();
    $forB = Source::factory()->team($teamB)->create();

    $visibleToA = Source::visibleTo($teamA)->pluck('id');

    expect($visibleToA)->toContain($global->id)
        ->toContain($forA->id)
        ->not->toContain($forB->id);
});
