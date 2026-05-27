<?php

use App\Models\Company;
use App\Models\PressRelease;
use App\Models\PublicationItem;
use App\Models\Source;
use App\Models\Team;
use App\Services\Matching\MatchCandidateFinder;

function setupRelease(Team $team, array $entities, array $topics): PressRelease
{
    $company = Company::factory()->create(['team_id' => $team->id]);

    return PressRelease::factory()->create([
        'company_id' => $company->id,
        'analysis_status' => PressRelease::ANALYSIS_DONE,
        'extracted_entities' => $entities,
        'extracted_topics' => $topics,
    ]);
}

it('returns publication items with overlapping signals', function () {
    $team = Team::factory()->create();
    $release = setupRelease($team, ['commodities' => ['gold'], 'jurisdictions' => ['Nevada']], ['drill-results']);

    $matchingSource = Source::factory()->create();
    $matching = PublicationItem::factory()->create([
        'source_id' => $matchingSource->id,
        'analysis_status' => PublicationItem::ANALYSIS_DONE,
        'extracted_topics' => ['gold', 'nevada'],
        'extracted_entities' => ['commodities' => ['gold'], 'jurisdictions' => []],
        'published_at' => now()->subDays(7),
    ]);

    $unrelated = PublicationItem::factory()->create([
        'source_id' => $matchingSource->id,
        'analysis_status' => PublicationItem::ANALYSIS_DONE,
        'extracted_topics' => ['cannabis'],
        'extracted_entities' => ['commodities' => ['marijuana']],
        'published_at' => now()->subDays(3),
    ]);

    $candidates = (new MatchCandidateFinder)->findFor($release, $team);

    expect($candidates->pluck('id')->all())->toContain($matching->id)
        ->not->toContain($unrelated->id);
});

it('excludes items older than the recency window', function () {
    $team = Team::factory()->create();
    $release = setupRelease($team, ['commodities' => ['gold']], []);

    $source = Source::factory()->create();
    $old = PublicationItem::factory()->create([
        'source_id' => $source->id,
        'analysis_status' => PublicationItem::ANALYSIS_DONE,
        'extracted_topics' => ['gold'],
        'extracted_entities' => ['commodities' => ['gold']],
        'published_at' => now()->subDays(200),
    ]);
    $recent = PublicationItem::factory()->create([
        'source_id' => $source->id,
        'analysis_status' => PublicationItem::ANALYSIS_DONE,
        'extracted_topics' => ['gold'],
        'extracted_entities' => ['commodities' => ['gold']],
        'published_at' => now()->subDays(5),
    ]);

    $candidates = (new MatchCandidateFinder(recencyDays: 90))->findFor($release, $team);

    expect($candidates->pluck('id')->all())->toContain($recent->id)
        ->not->toContain($old->id);
});

it('does not surface another team\'s private sources', function () {
    $teamA = Team::factory()->create();
    $teamB = Team::factory()->create();
    $release = setupRelease($teamA, ['commodities' => ['gold']], []);

    $teamBSource = Source::factory()->team($teamB)->create();
    $foreignItem = PublicationItem::factory()->create([
        'source_id' => $teamBSource->id,
        'analysis_status' => PublicationItem::ANALYSIS_DONE,
        'extracted_topics' => ['gold'],
        'extracted_entities' => ['commodities' => ['gold']],
        'published_at' => now()->subDay(),
    ]);

    $candidates = (new MatchCandidateFinder)->findFor($release, $teamA);

    expect($candidates->pluck('id')->all())->not->toContain($foreignItem->id);
});

it('only considers items with analysis done', function () {
    $team = Team::factory()->create();
    $release = setupRelease($team, ['commodities' => ['gold']], []);

    $source = Source::factory()->create();
    PublicationItem::factory()->create([
        'source_id' => $source->id,
        'analysis_status' => PublicationItem::ANALYSIS_PENDING,
        'extracted_topics' => ['gold'],
        'published_at' => now()->subDay(),
    ]);

    $candidates = (new MatchCandidateFinder)->findFor($release, $team);

    expect($candidates)->toHaveCount(0);
});
