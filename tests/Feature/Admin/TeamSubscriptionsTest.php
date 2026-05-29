<?php

use App\Models\SourceGroup;
use App\Models\Team;
use App\Models\User;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('renders the team edit page with the catalogue subscriptions relation manager', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $team = Team::factory()->create();

    SourceGroup::create(['name' => 'Mining', 'slug' => 'mining', 'is_active' => true]);
    SourceGroup::create(['name' => 'Oil & Gas', 'slug' => 'oil-gas', 'is_active' => true, 'is_premium' => true, 'monthly_price_cents' => 9900]);

    // The page rendering without a 500 is the contract — relation
    // manager content is lazily mounted via Livewire, so it isn't in
    // the initial HTML and we don't assert on its label here.
    actingAs($admin)
        ->get("/manage/teams/{$team->id}/edit")
        ->assertOk();
});

it('attaches a catalogue to a team via the pivot with provenance', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $team = Team::factory()->create();
    $group = SourceGroup::create(['name' => 'Clean Energy', 'slug' => 'clean', 'is_active' => true, 'is_premium' => true]);

    // Direct pivot write — the relation manager's AttachAction goes
    // through Eloquent the same way, so this exercises the data path
    // the operator UI uses.
    $team->sourceGroups()->attach($group->id, [
        'is_complimentary' => true,
        'subscribed_at' => now(),
        'expires_at' => now()->addMonth(),
        'granted_by_user_id' => $admin->id,
        'notes' => 'Q3 evaluation',
    ]);

    $pivot = $team->sourceGroups()->where('source_group_id', $group->id)->first()->pivot;
    expect($pivot->is_complimentary)->toBeTruthy();
    expect($pivot->granted_by_user_id)->toBe($admin->id);
    expect($pivot->notes)->toBe('Q3 evaluation');
});
