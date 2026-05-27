<?php

use App\Jobs\AnalyzePressReleaseJob;
use App\Models\PressRelease;
use App\Services\Llm\FakeLlmClient;
use App\Services\Llm\LlmClient;

beforeEach(function () {
    $this->llm = new FakeLlmClient;
    $this->app->instance(LlmClient::class, $this->llm);
});

it('analyzes a press release and persists extracted features', function () {
    $release = PressRelease::factory()->create([
        'title' => 'Aurelian intersects 12.4 g/t Au over 28m',
        'body_text' => 'Hole AUR-26-001 returned an intercept of 12.4 g/t Au over 28m within the Nevada Big Sky project.',
    ]);

    $this->llm->queue(json_encode([
        'entities' => [
            'commodities' => ['gold'],
            'jurisdictions' => ['Nevada'],
            'projects' => ['Big Sky'],
            'people' => [],
        ],
        'topics' => ['drill-results', 'gold', 'nevada'],
        'stance' => 'bullish',
        'key_claims' => [
            ['claim' => '12.4 g/t Au over 28m at Big Sky', 'type' => 'result'],
        ],
    ]));

    dispatch_sync(new AnalyzePressReleaseJob($release->id));

    $release->refresh();
    expect($release->analysis_status)->toBe(PressRelease::ANALYSIS_DONE)
        ->and($release->stance)->toBe('bullish')
        ->and($release->extracted_topics)->toBe(['drill-results', 'gold', 'nevada'])
        ->and($release->extracted_entities['commodities'])->toBe(['gold'])
        ->and($release->key_claims)->toHaveCount(1)
        ->and($release->analyzed_at)->not->toBeNull();
});

it('marks analysis failed when the LLM response is unparseable', function () {
    $release = PressRelease::factory()->create();

    $this->llm->queue('this is not json');

    expect(fn () => dispatch_sync(new AnalyzePressReleaseJob($release->id)))
        ->toThrow(\RuntimeException::class);

    expect($release->fresh()->analysis_status)->toBe(PressRelease::ANALYSIS_FAILED);
});

it('skips press releases that are already analyzed', function () {
    $release = PressRelease::factory()->create([
        'analysis_status' => PressRelease::ANALYSIS_DONE,
    ]);

    dispatch_sync(new AnalyzePressReleaseJob($release->id));

    expect($this->llm->calls)->toBeEmpty();
});

it('tolerates fenced JSON output', function () {
    $release = PressRelease::factory()->create();

    $this->llm->queue("```json\n".json_encode([
        'entities' => [], 'topics' => ['x'], 'stance' => 'neutral', 'key_claims' => [],
    ])."\n```");

    dispatch_sync(new AnalyzePressReleaseJob($release->id));

    expect($release->fresh()->extracted_topics)->toBe(['x']);
});
