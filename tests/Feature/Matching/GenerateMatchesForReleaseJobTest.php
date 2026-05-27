<?php

use App\Jobs\GenerateMatchesForReleaseJob;
use App\Models\Company;
use App\Models\MatchRecord;
use App\Models\PressRelease;
use App\Models\PublicationItem;
use App\Models\Source;
use App\Models\Team;
use App\Services\Llm\FakeLlmClient;
use App\Services\Llm\LlmClient;

beforeEach(function () {
    $this->llm = new FakeLlmClient;
    $this->app->instance(LlmClient::class, $this->llm);
});

function makeMatchScenario(): array
{
    $team = Team::factory()->create(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $team->id]);
    $release = PressRelease::factory()->create([
        'company_id' => $company->id,
        'analysis_status' => PressRelease::ANALYSIS_DONE,
        'extracted_entities' => ['commodities' => ['gold'], 'jurisdictions' => ['Nevada']],
        'extracted_topics' => ['drill-results'],
        'stance' => 'bullish',
        'key_claims' => [['claim' => '12.4 g/t Au over 28m', 'type' => 'result']],
    ]);

    $source = Source::factory()->create(['name' => 'Mining News']);
    $candidate = PublicationItem::factory()->create([
        'source_id' => $source->id,
        'analysis_status' => PublicationItem::ANALYSIS_DONE,
        'extracted_topics' => ['gold', 'nevada'],
        'extracted_entities' => ['commodities' => ['gold']],
        'published_at' => now()->subDays(7),
    ]);

    return ['team' => $team, 'release' => $release, 'candidate' => $candidate];
}

it('creates match records from LLM picks above the confidence threshold', function () {
    ['release' => $release, 'candidate' => $candidate] = makeMatchScenario();

    $this->llm->queue(json_encode([
        [
            'publication_item_id' => $candidate->id,
            'score' => 0.82,
            'rationale' => 'Recently covered gold-Nevada drilling; would respond to fresh intercept data.',
            'suggested_angle' => 'Offer a CEO interview comparing this intercept to their March piece.',
            'citations' => [['publication_item_id' => $candidate->id, 'quote' => 'gold-Nevada coverage']],
        ],
    ]));

    dispatch_sync(new GenerateMatchesForReleaseJob($release->id));

    $match = MatchRecord::first();
    expect($match)->not->toBeNull()
        ->and($match->score)->toBe(0.82)
        ->and($match->rationale_md)->toContain('gold-Nevada')
        ->and($match->suggested_angle_md)->toContain('CEO interview')
        ->and($match->publication_item_id)->toBe($candidate->id)
        ->and($match->press_release_id)->toBe($release->id)
        ->and($match->status)->toBe(MatchRecord::STATUS_NEW);
});

it('drops picks below the confidence threshold', function () {
    ['release' => $release, 'candidate' => $candidate] = makeMatchScenario();
    config(['match.min_confidence' => 0.7]);

    $this->llm->queue(json_encode([
        [
            'publication_item_id' => $candidate->id,
            'score' => 0.4,
            'rationale' => 'Loose overlap only.',
            'suggested_angle' => 'Maybe.',
            'citations' => [],
        ],
    ]));

    dispatch_sync(new GenerateMatchesForReleaseJob($release->id));

    expect(MatchRecord::count())->toBe(0);
});

it('ignores hallucinated publication_item_ids', function () {
    ['release' => $release, 'candidate' => $candidate] = makeMatchScenario();

    $this->llm->queue(json_encode([
        ['publication_item_id' => 999999, 'score' => 0.95, 'rationale' => 'x', 'suggested_angle' => 'y', 'citations' => []],
        ['publication_item_id' => $candidate->id, 'score' => 0.8, 'rationale' => 'real', 'suggested_angle' => 'z', 'citations' => []],
    ]));

    dispatch_sync(new GenerateMatchesForReleaseJob($release->id));

    expect(MatchRecord::count())->toBe(1)
        ->and(MatchRecord::first()->publication_item_id)->toBe($candidate->id);
});

it('is idempotent on re-run for the same release+item pair', function () {
    ['release' => $release, 'candidate' => $candidate] = makeMatchScenario();

    $payload = json_encode([
        ['publication_item_id' => $candidate->id, 'score' => 0.7, 'rationale' => 'first', 'suggested_angle' => 's1', 'citations' => []],
    ]);
    $payload2 = json_encode([
        ['publication_item_id' => $candidate->id, 'score' => 0.9, 'rationale' => 'updated', 'suggested_angle' => 's2', 'citations' => []],
    ]);

    $this->llm->queue($payload);
    dispatch_sync(new GenerateMatchesForReleaseJob($release->id));

    $this->llm->queue($payload2);
    dispatch_sync(new GenerateMatchesForReleaseJob($release->id));

    expect(MatchRecord::count())->toBe(1)
        ->and(MatchRecord::first()->rationale_md)->toBe('updated')
        ->and(MatchRecord::first()->score)->toBe(0.9);
});

it('skips release whose analysis is not done', function () {
    $team = Team::factory()->create();
    $company = Company::factory()->create(['team_id' => $team->id]);
    $release = PressRelease::factory()->create([
        'company_id' => $company->id,
        'analysis_status' => PressRelease::ANALYSIS_PENDING,
    ]);

    dispatch_sync(new GenerateMatchesForReleaseJob($release->id));

    expect(MatchRecord::count())->toBe(0)
        ->and($this->llm->calls)->toBeEmpty();
});
