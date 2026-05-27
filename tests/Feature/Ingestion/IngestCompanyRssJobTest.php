<?php

use App\Jobs\AnalyzePressReleaseJob;
use App\Jobs\IngestCompanyRssJob;
use App\Models\Company;
use App\Models\PressRelease;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

function fakeFeed(array $items): string
{
    $entries = '';
    foreach ($items as $item) {
        $entries .= "<item><title>{$item['title']}</title><link>{$item['link']}</link><guid>{$item['guid']}</guid><pubDate>{$item['pub']}</pubDate><description>{$item['desc']}</description></item>";
    }

    return "<?xml version=\"1.0\"?><rss version=\"2.0\"><channel><title>Feed</title>{$entries}</channel></rss>";
}

beforeEach(function () {
    Bus::fake([AnalyzePressReleaseJob::class]);
});

it('ingests new press releases and dispatches analysis jobs', function () {
    $company = Company::factory()->create(['rss_feed_url' => 'https://co.example/rss']);

    Http::fake([
        'co.example/rss' => Http::response(fakeFeed([
            ['title' => 'PR One', 'link' => 'https://co.example/1', 'guid' => 'g-1', 'pub' => 'Wed, 20 May 2026 10:00:00 GMT', 'desc' => 'First release'],
            ['title' => 'PR Two', 'link' => 'https://co.example/2', 'guid' => 'g-2', 'pub' => 'Thu, 21 May 2026 10:00:00 GMT', 'desc' => 'Second release'],
        ])),
    ]);

    dispatch_sync(new IngestCompanyRssJob($company->id));

    expect(PressRelease::count())->toBe(2);
    Bus::assertDispatched(AnalyzePressReleaseJob::class, 2);
});

it('does not re-create existing releases on re-ingest', function () {
    $company = Company::factory()->create(['rss_feed_url' => 'https://co.example/rss']);

    Http::fake([
        'co.example/rss' => Http::response(fakeFeed([
            ['title' => 'PR One', 'link' => 'https://co.example/1', 'guid' => 'g-1', 'pub' => 'Wed, 20 May 2026 10:00:00 GMT', 'desc' => 'd1'],
        ])),
    ]);

    dispatch_sync(new IngestCompanyRssJob($company->id));
    dispatch_sync(new IngestCompanyRssJob($company->id));

    expect(PressRelease::count())->toBe(1);
    Bus::assertDispatched(AnalyzePressReleaseJob::class, 1);
});

it('updates last_ingested_at on the company', function () {
    $company = Company::factory()->create(['rss_feed_url' => 'https://co.example/rss', 'last_ingested_at' => null]);
    Http::fake(['co.example/rss' => Http::response(fakeFeed([]))]);

    dispatch_sync(new IngestCompanyRssJob($company->id));

    expect($company->fresh()->last_ingested_at)->not->toBeNull();
});

it('skips inactive companies', function () {
    $company = Company::factory()->create([
        'rss_feed_url' => 'https://co.example/rss',
        'is_active' => false,
    ]);

    Http::fake(['co.example/rss' => Http::response(fakeFeed([
        ['title' => 'PR', 'link' => 'https://co.example/1', 'guid' => 'g-1', 'pub' => 'Wed, 20 May 2026 10:00:00 GMT', 'desc' => 'd'],
    ]))]);

    dispatch_sync(new IngestCompanyRssJob($company->id));

    expect(PressRelease::count())->toBe(0);
});

it('skips items missing title or guid', function () {
    $company = Company::factory()->create(['rss_feed_url' => 'https://co.example/rss']);

    Http::fake([
        'co.example/rss' => Http::response(fakeFeed([
            ['title' => '', 'link' => 'https://co.example/x', 'guid' => 'g-1', 'pub' => 'Wed, 20 May 2026 10:00:00 GMT', 'desc' => 'no title'],
            ['title' => 'Good', 'link' => 'https://co.example/2', 'guid' => 'g-2', 'pub' => 'Thu, 21 May 2026 10:00:00 GMT', 'desc' => 'has title'],
        ])),
    ]);

    dispatch_sync(new IngestCompanyRssJob($company->id));

    expect(PressRelease::count())->toBe(1)
        ->and(PressRelease::first()->title)->toBe('Good');
});
