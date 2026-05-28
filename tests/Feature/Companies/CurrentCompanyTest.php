<?php

use App\Models\Company;
use App\Models\MatchRecord;
use App\Models\PressRelease;
use App\Models\PublicationItem;
use App\Models\Source;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\put;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('defaults the current company to the first company in the active team', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $team = $user->currentTeam;

    $first = Company::factory()->create(['team_id' => $team->id, 'name' => 'Alpha Mining']);
    Company::factory()->create(['team_id' => $team->id, 'name' => 'Bravo Resources']);

    // No current_company_id stored — resolver should fall back to first.
    expect($user->current_company_id)->toBeNull();
    expect($user->resolveCurrentCompany()->is($first))->toBeTrue();
    expect($user->fresh()->current_company_id)->toBe($first->id);
});

it('refuses to switch to a company outside the active team', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $ownCompany = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    $otherTeamCompany = Company::factory()->create(); // independent team

    expect($user->switchCompany($otherTeamCompany))->toBeFalse();
    expect($user->switchCompany($ownCompany))->toBeTrue();
    expect($user->fresh()->current_company_id)->toBe($ownCompany->id);
});

it('clears the current company when the active team changes', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $user->switchCompany($company);

    $secondTeam = $user->ownedTeams()->create([
        'name' => 'Second',
        'personal_team' => false,
        'is_active' => true,
    ]);

    $user->switchTeam($secondTeam);

    expect($user->fresh()->current_company_id)->toBeNull();
});

it('scopes the matches index to the current company', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $team = $user->currentTeam;

    $companyA = Company::factory()->create(['team_id' => $team->id, 'name' => 'Alpha']);
    $companyB = Company::factory()->create(['team_id' => $team->id, 'name' => 'Bravo']);

    $matchA = makeMatchFor($companyA, headline: 'Headline-Alpha');
    $matchB = makeMatchFor($companyB, headline: 'Headline-Bravo');

    actingAs($user);
    $user->switchCompany($companyA);

    $response = $this->get(route('matches.index'));

    $response->assertOk();
    $response->assertSeeText('Headline-Alpha');
    $response->assertDontSeeText('Headline-Bravo');
});

it('pins focus when visiting a company-scoped page', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $team = $user->currentTeam;

    $companyA = Company::factory()->create(['team_id' => $team->id]);
    $companyB = Company::factory()->create(['team_id' => $team->id]);
    $user->switchCompany($companyA);

    actingAs($user);
    $this->get(route('companies.library', $companyB))->assertOk();

    expect($user->fresh()->current_company_id)->toBe($companyB->id);
});

it('switches the current company via the controller and redirects to matches', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $target = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    actingAs($user);

    put(route('current-company.update'), ['company_id' => $target->id])
        ->assertRedirect(route('companies.matches', $target));

    expect($user->fresh()->current_company_id)->toBe($target->id);
});

it('forbids switching to a company in another team via the controller', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $otherCompany = Company::factory()->create(); // independent team

    actingAs($user);

    put(route('current-company.update'), ['company_id' => $otherCompany->id])
        ->assertForbidden();
});

/**
 * Build a minimal match attached to the given company. The matches index
 * view renders the press-release title, so we set it to a unique string the
 * test can assert against.
 */
function makeMatchFor(Company $company, string $headline): MatchRecord
{
    // matches.index renders publicationItem->title, so the headline goes there.
    $source = Source::factory()->create();
    $item = PublicationItem::factory()->create([
        'source_id' => $source->id,
        'title' => $headline,
    ]);
    $release = PressRelease::factory()->create(['company_id' => $company->id]);

    return MatchRecord::factory()->create([
        'company_id' => $company->id,
        'press_release_id' => $release->id,
        'publication_item_id' => $item->id,
        'status' => MatchRecord::STATUS_NEW,
    ]);
}
