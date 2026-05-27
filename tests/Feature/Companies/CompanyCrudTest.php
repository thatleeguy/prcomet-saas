<?php

use App\Livewire\Companies;
use App\Models\Company;
use App\Models\User;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('lists companies for the current team only', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $mine = Company::factory()->create(['team_id' => $user->currentTeam->id, 'name' => 'My JMC']);
    $theirs = Company::factory()->create(['name' => 'Their JMC']);

    Livewire::actingAs($user)
        ->test(Companies\Index::class)
        ->assertSee('My JMC')
        ->assertDontSee('Their JMC');
});

it('blocks creating a company beyond the seat limit', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 1]);
    Company::factory()->create(['team_id' => $user->currentTeam->id]);

    Livewire::actingAs($user)
        ->test(Companies\Edit::class)
        ->set('name', 'Overflow JMC')
        ->call('save')
        ->assertHasErrors(['name']);

    expect(Company::where('name', 'Overflow JMC')->exists())->toBeFalse();
});

it('creates a company under the seat limit and normalises tags', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);

    Livewire::actingAs($user)
        ->test(Companies\Edit::class)
        ->set('name', 'Aurelian Mining')
        ->set('ticker', 'AUR')
        ->set('rss_feed_url', 'https://aurelian.example/rss')
        ->set('sector_tags_csv', 'Gold, copper , GOLD, ,Nevada')
        ->call('save')
        ->assertRedirect();

    $company = Company::where('name', 'Aurelian Mining')->firstOrFail();
    expect($company->team_id)->toBe($user->currentTeam->id)
        ->and($company->ticker)->toBe('AUR')
        ->and($company->sector_tags)->toBe(['gold', 'copper', 'nevada']);
});

it('updates an existing company', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 1]);
    $company = Company::factory()->create([
        'team_id' => $user->currentTeam->id,
        'name' => 'Old Name',
    ]);

    Livewire::actingAs($user)
        ->test(Companies\Edit::class, ['company' => $company])
        ->set('name', 'New Name')
        ->call('save');

    expect($company->fresh()->name)->toBe('New Name');
});

it('forbids editing another team\'s company', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $foreign = Company::factory()->create();

    actingAs($user)
        ->get(route('companies.edit', $foreign))
        ->assertForbidden();
});

it('shows a company detail page', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 1]);
    $company = Company::factory()->create([
        'team_id' => $user->currentTeam->id,
        'name' => 'Detail JMC',
    ]);

    actingAs($user)
        ->get(route('companies.show', $company))
        ->assertOk()
        ->assertSee('Detail JMC');
});

it('forbids viewing another team\'s company', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $foreign = Company::factory()->create();

    actingAs($user)
        ->get(route('companies.show', $foreign))
        ->assertForbidden();
});

it('blocks the companies index when team is not active', function () {
    $user = makeUserWithTeam(); // inactive

    actingAs($user)
        ->get(route('companies.index'))
        ->assertRedirect(route('teams.pending'));
});
