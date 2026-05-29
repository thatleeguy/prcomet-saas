<?php

use App\Models\Source;
use App\Models\SourceGroup;
use App\Models\Team;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('shows sources from groups the team subscribes to', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $team = $user->currentTeam;

    $mining = SourceGroup::create([
        'name' => 'Mining Publications',
        'slug' => 'mining',
        'is_active' => true,
    ]);
    $oilGas = SourceGroup::create([
        'name' => 'Oil & Gas',
        'slug' => 'oil-gas',
        'is_active' => true,
    ]);

    $miningSource = Source::factory()->create([
        'scope' => Source::SCOPE_GLOBAL,
        'source_group_id' => $mining->id,
    ]);
    Source::factory()->create([
        'scope' => Source::SCOPE_GLOBAL,
        'source_group_id' => $oilGas->id,
    ]);

    $team->sourceGroups()->attach($mining->id, ['subscribed_at' => now()]);

    $visible = Source::visibleTo($team)->pluck('id')->all();

    expect($visible)->toContain($miningSource->id);
    expect($visible)->not->toContain(Source::where('source_group_id', $oilGas->id)->value('id'));
});

it('hides sources from groups after the team unsubscribes', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $team = $user->currentTeam;

    $group = SourceGroup::create(['name' => 'Mining', 'slug' => 'm', 'is_active' => true]);
    $source = Source::factory()->create([
        'scope' => Source::SCOPE_GLOBAL,
        'source_group_id' => $group->id,
    ]);

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
        'source_group_id' => null,
    ]);

    expect(Source::visibleTo($team)->pluck('id'))->toContain($privateSource->id);
});

it('hides one team\'s subscribed sources from another team', function () {
    $teamA = makeUserWithTeam(['is_active' => true])->currentTeam;
    $teamB = makeUserWithTeam(['is_active' => true])->currentTeam;

    $premium = SourceGroup::create(['name' => 'Premium', 'slug' => 'p', 'is_active' => true, 'is_premium' => true]);
    $source = Source::factory()->create([
        'scope' => Source::SCOPE_GLOBAL,
        'source_group_id' => $premium->id,
    ]);

    $teamA->sourceGroups()->attach($premium->id, ['subscribed_at' => now()]);

    expect(Source::visibleTo($teamA)->pluck('id'))->toContain($source->id);
    expect(Source::visibleTo($teamB)->pluck('id'))->not->toContain($source->id);
});

it('drops trashed sources from the visible corpus but keeps the row intact', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $team = $user->currentTeam;

    $group = SourceGroup::create(['name' => 'Mining', 'slug' => 'm', 'is_active' => true]);
    $source = Source::factory()->create([
        'scope' => Source::SCOPE_GLOBAL,
        'source_group_id' => $group->id,
    ]);
    $team->sourceGroups()->attach($group->id, ['subscribed_at' => now()]);

    expect(Source::visibleTo($team)->pluck('id'))->toContain($source->id);

    $source->delete();

    expect(Source::visibleTo($team)->pluck('id'))->not->toContain($source->id);
    // The row itself stays — protects matches that reference it via publication items.
    expect(Source::withTrashed()->find($source->id))->not->toBeNull();
});

it('respects subscription expiry by hiding expired group sources', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $team = $user->currentTeam;

    $group = SourceGroup::create(['name' => 'Mining', 'slug' => 'm', 'is_active' => true]);
    $source = Source::factory()->create([
        'scope' => Source::SCOPE_GLOBAL,
        'source_group_id' => $group->id,
    ]);

    $team->sourceGroups()->attach($group->id, [
        'subscribed_at' => now()->subDays(30),
        'expires_at' => now()->subDay(), // expired yesterday
    ]);

    expect(Source::visibleTo($team)->pluck('id'))->not->toContain($source->id);
});
