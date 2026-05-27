<?php

use App\Jobs\FetchPlacementMetadataJob;
use App\Livewire\Matches;
use App\Livewire\Wins;
use App\Models\Company;
use App\Models\MatchRecord;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('attaches a placement URL and flips the match to placed', function () {
    Bus::fake([FetchPlacementMetadataJob::class]);

    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 1]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $match = MatchRecord::factory()->create(['company_id' => $company->id]);

    Livewire::actingAs($user)
        ->test(Matches\Show::class, ['match' => $match])
        ->set('placementUrl', 'https://example.com/our-ceo-interview')
        ->call('attachPlacement');

    $match->refresh();
    expect($match->status)->toBe(MatchRecord::STATUS_PLACED)
        ->and($match->placement_url)->toBe('https://example.com/our-ceo-interview');

    Bus::assertDispatched(FetchPlacementMetadataJob::class);
});

it('rejects an invalid placement URL', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 1]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $match = MatchRecord::factory()->create(['company_id' => $company->id]);

    Livewire::actingAs($user)
        ->test(Matches\Show::class, ['match' => $match])
        ->set('placementUrl', 'not-a-url')
        ->call('attachPlacement')
        ->assertHasErrors(['placementUrl']);

    expect($match->fresh()->placement_url)->toBeNull();
});

it('fetches OpenGraph title and description for a placement', function () {
    $match = MatchRecord::factory()->create([
        'placement_url' => 'https://example.com/article',
    ]);

    Http::fake([
        'example.com/article' => Http::response(<<<HTML
        <html>
        <head>
          <meta property="og:title" content="JMC Insider: Aurelian's Big Sky play" />
          <meta property="og:description" content="A look at the latest drill program." />
          <meta property="article:published_time" content="2026-05-15T10:00:00Z" />
          <title>Should be ignored when og:title present</title>
        </head>
        </html>
        HTML),
    ]);

    dispatch_sync(new FetchPlacementMetadataJob($match->id));

    $match->refresh();
    expect($match->placement_title)->toBe("JMC Insider: Aurelian's Big Sky play")
        ->and($match->placement_description)->toBe('A look at the latest drill program.')
        ->and($match->placement_published_at?->toDateString())->toBe('2026-05-15')
        ->and($match->placement_fetched_at)->not->toBeNull();
});

it('falls back to <title> when og:title is missing', function () {
    $match = MatchRecord::factory()->create([
        'placement_url' => 'https://example.com/article',
    ]);

    Http::fake([
        'example.com/article' => Http::response('<html><head><title>Plain Title</title></head></html>'),
    ]);

    dispatch_sync(new FetchPlacementMetadataJob($match->id));

    expect($match->fresh()->placement_title)->toBe('Plain Title');
});

it('silences a failed fetch but records the attempt', function () {
    $match = MatchRecord::factory()->create([
        'placement_url' => 'https://example.com/article',
    ]);

    Http::fake([
        'example.com/article' => Http::response('error', 500),
    ]);

    dispatch_sync(new FetchPlacementMetadataJob($match->id));

    expect($match->fresh()->placement_fetched_at)->not->toBeNull()
        ->and($match->fresh()->placement_title)->toBeNull();
});

it('lists placed matches in the wins dashboard', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    $placed = MatchRecord::factory()->create([
        'company_id' => $company->id,
        'status' => MatchRecord::STATUS_PLACED,
        'placement_url' => 'https://example.com/our-win',
        'placement_title' => 'Our team won placement',
    ]);
    $contacted = MatchRecord::factory()->create([
        'company_id' => $company->id,
        'status' => MatchRecord::STATUS_CONTACTED,
        'rationale_md' => 'Contacted only — should NOT appear in Wins',
    ]);

    Livewire::actingAs($user)
        ->test(Wins\Index::class)
        ->assertSee('Our team won placement')
        ->assertDontSee('Contacted only — should NOT appear in Wins');
});

it('isolates wins per team', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);

    $foreignCompany = Company::factory()->create();
    MatchRecord::factory()->create([
        'company_id' => $foreignCompany->id,
        'status' => MatchRecord::STATUS_PLACED,
        'placement_title' => 'Foreign team win',
    ]);

    Livewire::actingAs($user)
        ->test(Wins\Index::class)
        ->assertDontSee('Foreign team win');
});

it('blocks the wins page when team is inactive', function () {
    $user = makeUserWithTeam();

    actingAs($user)
        ->get(route('wins.index'))
        ->assertRedirect(route('teams.pending'));
});
