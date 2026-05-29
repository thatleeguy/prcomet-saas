<?php

use App\Models\Source;
use App\Models\SourceGroup;
use App\Models\Team;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('shows sources from groups the team subscribes to', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $team = $user->currentTeam;

    $mining = SourceGroup::create(['name' => 'Mining Publications', 'slug' => 'mining', 'is_active' => true]);
    $oilGas = SourceGroup::create(['name' => 'Oil & Gas', 'slug' => 'oil-gas', 'is_active' => true]);

    $miningSource = Source::factory()->create(['scope' => Source::SCOPE_GLOBAL]);
    $oilSource = Source::factory()->create(['scope' => Source::SCOPE_GLOBAL]);

    $mining->sources()->attach($miningSource->id);
    $oilGas->sources()->attach($oilSource->id);

    $team->sourceGroups()->attach($mining->id, ['subscribed_at' => now()]);

    $visible = Source::visibleTo($team)->pluck('id')->all();

    expect($visible)->toContain($miningSource->id);
    expect($visible)->not->toContain($oilSource->id);
});

it('keeps a multi-catalogue source visible when any subscribed catalogue contains it', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $team = $user->currentTeam;

    $mining = SourceGroup::create(['name' => 'Mining', 'slug' => 'm', 'is_active' => true]);
    $clean = SourceGroup::create(['name' => 'Clean Energy', 'slug' => 'c', 'is_active' => true, 'is_premium' => true]);

    // One source row, tagged into both catalogues.
    $shared = Source::factory()->create(['scope' => Source::SCOPE_GLOBAL]);
    $shared->sourceGroups()->attach([$mining->id, $clean->id]);

    // Team only subscribes to Mining — they should still see the source.
    $team->sourceGroups()->attach($mining->id, ['subscribed_at' => now()]);

    expect(Source::visibleTo($team)->pluck('id'))->toContain($shared->id);
});

it('hides sources from groups after the team unsubscribes', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $team = $user->currentTeam;

    $group = SourceGroup::create(['name' => 'Mining', 'slug' => 'm', 'is_active' => true]);
    $source = Source::factory()->create(['scope' => Source::SCOPE_GLOBAL]);
    $group->sources()->attach($source->id);

    $team->sourceGroups()->attach($group->id, ['subscribed_at' => now()]);
    expect(Source::visibleTo($team)->pluck('id'))->toContain($source->id);

    $team->sourceGroups()->detach($group->id);
    expect(Source::visibleTo($team->fresh())->pluck('id'))->not->toContain($source->id);
});

it('still shows the team\'s private (scope=team) sources alongside catalog ones', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $team = $user->currentTeam;

    $privateSource = Source::factory()->create([
        'scope' => Source::SCOPE_TEAM,
        'team_id' => $team->id,
    ]);

    expect(Source::visibleTo($team)->pluck('id'))->toContain($privateSource->id);
});

it('hides one team\'s subscribed sources from another team', function () {
    $teamA = makeUserWithTeam(['is_active' => true])->currentTeam;
    $teamB = makeUserWithTeam(['is_active' => true])->currentTeam;

    $premium = SourceGroup::create(['name' => 'Premium', 'slug' => 'p', 'is_active' => true, 'is_premium' => true]);
    $source = Source::factory()->create(['scope' => Source::SCOPE_GLOBAL]);
    $premium->sources()->attach($source->id);

    $teamA->sourceGroups()->attach($premium->id, ['subscribed_at' => now()]);

    expect(Source::visibleTo($teamA)->pluck('id'))->toContain($source->id);
    expect(Source::visibleTo($teamB)->pluck('id'))->not->toContain($source->id);
});

it('drops trashed sources from the visible corpus but keeps the row intact', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $team = $user->currentTeam;

    $group = SourceGroup::create(['name' => 'Mining', 'slug' => 'm', 'is_active' => true]);
    $source = Source::factory()->create(['scope' => Source::SCOPE_GLOBAL]);
    $group->sources()->attach($source->id);
    $team->sourceGroups()->attach($group->id, ['subscribed_at' => now()]);

    expect(Source::visibleTo($team)->pluck('id'))->toContain($source->id);

    $source->delete();

    expect(Source::visibleTo($team)->pluck('id'))->not->toContain($source->id);
    expect(Source::withTrashed()->find($source->id))->not->toBeNull();
});

it('respects subscription expiry by hiding expired group sources', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $team = $user->currentTeam;

    $group = SourceGroup::create(['name' => 'Mining', 'slug' => 'm', 'is_active' => true]);
    $source = Source::factory()->create(['scope' => Source::SCOPE_GLOBAL]);
    $group->sources()->attach($source->id);

    $team->sourceGroups()->attach($group->id, [
        'subscribed_at' => now()->subDays(30),
        'expires_at' => now()->subDay(),
    ]);

    expect(Source::visibleTo($team)->pluck('id'))->not->toContain($source->id);
});

it('shows global sources not in any catalogue (legacy fallback)', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $team = $user->currentTeam;

    // Source has no catalogue pivot rows — should still be visible to
    // every team so a pre-Catalog install isn't dark.
    $legacy = Source::factory()->create(['scope' => Source::SCOPE_GLOBAL]);

    expect(Source::visibleTo($team)->pluck('id'))->toContain($legacy->id);
});

it('a source can be tagged into multiple catalogues without duplication', function () {
    $group1 = SourceGroup::create(['name' => 'A', 'slug' => 'a', 'is_active' => true]);
    $group2 = SourceGroup::create(['name' => 'B', 'slug' => 'b', 'is_active' => true]);
    $group3 = SourceGroup::create(['name' => 'C', 'slug' => 'c', 'is_active' => true]);

    $source = Source::factory()->create(['scope' => Source::SCOPE_GLOBAL]);
    $source->sourceGroups()->attach([$group1->id, $group2->id, $group3->id]);

    expect(Source::count())->toBe(1);
    expect($source->sourceGroups()->count())->toBe(3);
    expect($group1->sources()->pluck('sources.id'))->toContain($source->id);
    expect($group2->sources()->pluck('sources.id'))->toContain($source->id);
    expect($group3->sources()->pluck('sources.id'))->toContain($source->id);
});
