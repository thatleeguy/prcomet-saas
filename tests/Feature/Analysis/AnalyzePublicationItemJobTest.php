<?php

use App\Jobs\AnalyzePublicationItemJob;
use App\Jobs\BuildAuthorProfileJob;
use App\Models\Author;
use App\Models\ExtractedClaim;
use App\Models\PublicationItem;
use App\Services\Llm\FakeLlmClient;
use App\Services\Llm\LlmClient;
use Illuminate\Support\Facades\Bus;

beforeEach(function () {
    $this->llm = new FakeLlmClient;
    $this->app->instance(LlmClient::class, $this->llm);
    Bus::fake([BuildAuthorProfileJob::class]);
});

it('analyzes a publication item, identifies the author, and stores claims', function () {
    $item = PublicationItem::factory()->create([
        'title' => 'Silver under $30 by Q3 — supply story is overrated',
    ]);

    $this->llm->queue(json_encode([
        'author' => ['name' => 'Jane Quant', 'x_handle' => '@janeq'],
        'entities' => ['commodities' => ['silver'], 'jurisdictions' => [], 'companies' => []],
        'topics' => ['silver', 'contrarian'],
        'stance' => 'bearish',
        'claims' => [
            [
                'claim' => 'Silver will trade under $30 by Q3 2026',
                'topic' => 'silver',
                'predicted_outcome' => 'under $30',
                'timeframe' => 'Q3 2026',
            ],
        ],
    ]));

    dispatch_sync(new AnalyzePublicationItemJob($item->id));

    $item->refresh();
    expect($item->analysis_status)->toBe(PublicationItem::ANALYSIS_DONE)
        ->and($item->stance)->toBe('bearish')
        ->and($item->extracted_topics)->toBe(['silver', 'contrarian']);

    $author = Author::where('name', 'Jane Quant')->first();
    expect($author)->not->toBeNull()
        ->and($author->x_handle)->toBe('@janeq')
        ->and($item->author_id)->toBe($author->id);

    $claim = ExtractedClaim::first();
    expect($claim->claim_text)->toBe('Silver will trade under $30 by Q3 2026')
        ->and($claim->predicted_outcome)->toBe('under $30')
        ->and($claim->timeframe)->toBe('Q3 2026')
        ->and($claim->author_id)->toBe($author->id);

    Bus::assertDispatched(BuildAuthorProfileJob::class, fn ($job) => $job->authorId === $author->id);
});

it('deduplicates authors by slug across items', function () {
    $itemA = PublicationItem::factory()->create();
    $itemB = PublicationItem::factory()->create();

    foreach (['itemA', 'itemB'] as $_) {
        $this->llm->queue(json_encode([
            'author' => ['name' => 'Jane Quant'],
            'entities' => [], 'topics' => [], 'stance' => 'neutral', 'claims' => [],
        ]));
    }

    dispatch_sync(new AnalyzePublicationItemJob($itemA->id));
    dispatch_sync(new AnalyzePublicationItemJob($itemB->id));

    expect(Author::count())->toBe(1);
});

it('replaces claims on re-analysis to stay idempotent', function () {
    $item = PublicationItem::factory()->create();

    $this->llm->queue(json_encode([
        'author' => null,
        'entities' => [], 'topics' => [], 'stance' => 'neutral',
        'claims' => [['claim' => 'Old claim', 'topic' => 'gold']],
    ]));
    dispatch_sync(new AnalyzePublicationItemJob($item->id));
    PublicationItem::where('id', $item->id)->update(['analysis_status' => PublicationItem::ANALYSIS_PENDING]);
    expect(ExtractedClaim::first()->claim_text)->toBe('Old claim');

    $this->llm->queue(json_encode([
        'author' => null,
        'entities' => [], 'topics' => [], 'stance' => 'neutral',
        'claims' => [['claim' => 'New claim', 'topic' => 'gold']],
    ]));
    dispatch_sync(new AnalyzePublicationItemJob($item->id));

    expect(ExtractedClaim::count())->toBe(1)
        ->and(ExtractedClaim::first()->claim_text)->toBe('New claim');
});
