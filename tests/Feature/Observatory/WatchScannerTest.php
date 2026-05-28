<?php

use App\Models\Company;
use App\Models\PublicationItem;
use App\Models\Source;
use App\Models\Watch;
use App\Models\WatchHit;
use App\Services\Observatory\WatchScanner;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = makeUserWithTeam(['is_active' => true]);
    $this->company = Company::factory()->create(['team_id' => $this->user->currentTeam->id]);
    $this->source = Source::factory()->create(['scope' => Source::SCOPE_GLOBAL]);
});

it('records a literal hit when a term appears in a publication item', function () {
    $item = PublicationItem::factory()->create([
        'source_id' => $this->source->id,
        'title' => 'Newmont reports record quarter at Cripple Creek',
        'body_text' => 'Production from the Cripple Creek & Victor mine exceeded analyst estimates.',
    ]);

    $watch = Watch::create([
        'company_id' => $this->company->id,
        'name' => 'Newmont',
        'kind' => Watch::KIND_COMPANY,
        'terms' => ['Newmont', 'Newmont Mining'],
        'mode' => Watch::MODE_LITERAL,
    ]);

    $created = app(WatchScanner::class)->scan($watch);

    expect($created)->toBe(1);

    $hit = WatchHit::firstWhere('watch_id', $watch->id);
    expect($hit)->not->toBeNull();
    expect($hit->matched_term)->toBe('Newmont');
    expect($hit->content_type)->toBe(WatchHit::TYPE_PUBLICATION_ITEM);
    expect($hit->content_id)->toBe($item->id);
    expect($hit->context_snippet)->toContain('Newmont');
});

it('uses whole-word matching so substrings do not trigger a hit', function () {
    PublicationItem::factory()->create([
        'source_id' => $this->source->id,
        'title' => 'Newmontville real estate roundup',
        'body_text' => 'A look at the Newmontville housing market.',
    ]);

    $watch = Watch::create([
        'company_id' => $this->company->id,
        'name' => 'Newmont',
        'kind' => Watch::KIND_COMPANY,
        'terms' => ['Newmont'],
        'mode' => Watch::MODE_LITERAL,
    ]);

    expect(app(WatchScanner::class)->scan($watch))->toBe(0);
    expect(WatchHit::count())->toBe(0);
});

it('is idempotent — re-scanning the same corpus does not duplicate hits', function () {
    PublicationItem::factory()->create([
        'source_id' => $this->source->id,
        'title' => 'Industry update',
        'body_text' => 'Newmont announced a new project.',
    ]);

    $watch = Watch::create([
        'company_id' => $this->company->id,
        'name' => 'Newmont',
        'kind' => Watch::KIND_COMPANY,
        'terms' => ['Newmont'],
        'mode' => Watch::MODE_LITERAL,
    ]);

    $scanner = app(WatchScanner::class);
    expect($scanner->scan($watch))->toBe(1);
    expect($scanner->scan($watch))->toBe(0);
    expect(WatchHit::count())->toBe(1);
});

it('does not scan press releases — scope is publication items only', function () {
    \App\Models\PressRelease::factory()->create([
        'company_id' => $this->company->id,
        'title' => 'Partnership announcement',
        'body_text' => 'Working with Newmont on a JV.',
    ]);

    $watch = Watch::create([
        'company_id' => $this->company->id,
        'name' => 'Newmont',
        'kind' => Watch::KIND_COMPANY,
        'terms' => ['Newmont'],
        'mode' => Watch::MODE_LITERAL,
    ]);

    // Even though "Newmont" appears in the team's own press release,
    // Observatory intentionally ignores it — the signal of interest
    // is "outside world saying X", not the company's own announcements.
    expect(app(WatchScanner::class)->scan($watch))->toBe(0);
    expect(WatchHit::count())->toBe(0);
});

it('does not scan content for other teams', function () {
    $otherTeamSource = Source::factory()->create([
        'scope' => Source::SCOPE_TEAM,
        'team_id' => \App\Models\Team::factory()->create()->id,
    ]);
    PublicationItem::factory()->create([
        'source_id' => $otherTeamSource->id,
        'title' => 'Newmont news',
        'body_text' => 'Newmont news for the other team.',
    ]);

    $watch = Watch::create([
        'company_id' => $this->company->id,
        'name' => 'Newmont',
        'kind' => Watch::KIND_COMPANY,
        'terms' => ['Newmont'],
        'mode' => Watch::MODE_LITERAL,
    ]);

    expect(app(WatchScanner::class)->scan($watch))->toBe(0);
});

it('downgrades literal_llm to literal when the team is not entitled', function () {
    $watch = Watch::create([
        'company_id' => $this->company->id,
        'name' => 'Newmont',
        'kind' => Watch::KIND_COMPANY,
        'terms' => ['Newmont'],
        'mode' => Watch::MODE_LITERAL_LLM,
    ]);

    expect($this->user->currentTeam->llmObservatoryEnabled())->toBeFalse();
    expect($watch->effectiveMode())->toBe(Watch::MODE_LITERAL);
    expect($watch->llmEnabled())->toBeFalse();

    // Flip the entitlement and the effective mode flips with it.
    $this->user->currentTeam->forceFill(['llm_observatory_enabled' => true])->save();
    $watch->refresh();
    expect($watch->effectiveMode())->toBe(Watch::MODE_LITERAL_LLM);
    expect($watch->llmEnabled())->toBeTrue();
});

it('bumps hit_count and last_matched_at on the watch when scan finds new hits', function () {
    PublicationItem::factory()->create([
        'source_id' => $this->source->id,
        'title' => 'Newmont news',
        'body_text' => 'Body.',
    ]);

    $watch = Watch::create([
        'company_id' => $this->company->id,
        'name' => 'Newmont',
        'kind' => Watch::KIND_COMPANY,
        'terms' => ['Newmont'],
        'mode' => Watch::MODE_LITERAL,
    ]);

    expect($watch->hit_count)->toBe(0);
    expect($watch->last_matched_at)->toBeNull();

    app(WatchScanner::class)->scan($watch);

    $watch->refresh();
    expect($watch->hit_count)->toBe(1);
    expect($watch->last_matched_at)->not->toBeNull();
});
