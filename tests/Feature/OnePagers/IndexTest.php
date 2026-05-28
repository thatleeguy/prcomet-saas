<?php

use App\Livewire\OnePagers\Index;
use App\Models\Company;
use App\Models\MatchRecord;
use App\Models\OnePager;
use App\Models\PressRelease;
use App\Models\PublicationItem;
use App\Models\Source;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('lists one-pagers for the requested company only', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $mine = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $other = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    makeOnePagerFor($mine, headline: 'Headline-Mine');
    makeOnePagerFor($other, headline: 'Headline-Other');

    Livewire::actingAs($user)
        ->test(Index::class, ['company' => $mine])
        ->assertSee('Headline-Mine')
        ->assertDontSee('Headline-Other');
});

it('forbids accessing another team\'s one-pagers', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $foreign = Company::factory()->create(); // independent team

    actingAs($user)
        ->get(route('companies.onepagers', $foreign))
        ->assertForbidden();
});

it('filters by status', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    makeOnePagerFor($company, headline: 'Headline-Published', status: OnePager::STATUS_PUBLISHED);
    makeOnePagerFor($company, headline: 'Headline-Draft', status: OnePager::STATUS_DRAFT);

    Livewire::actingAs($user)
        ->test(Index::class, ['company' => $company])
        ->call('setStatus', 'published')
        ->assertSee('Headline-Published')
        ->assertDontSee('Headline-Draft');
});

it('switches the current company on visit', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $a = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $b = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $user->switchCompany($a);

    actingAs($user)->get(route('companies.onepagers', $b))->assertOk();

    expect($user->fresh()->current_company_id)->toBe($b->id);
});

/**
 * Build a one-pager + its underlying match chain in a single helper so the
 * tests stay readable. Headline lands on publicationItem->title because
 * that's what the index renders for each row.
 */
function makeOnePagerFor(Company $company, string $headline, string $status = OnePager::STATUS_DRAFT): OnePager
{
    $source = Source::factory()->create();
    $item = PublicationItem::factory()->create([
        'source_id' => $source->id,
        'title' => $headline,
    ]);
    $release = PressRelease::factory()->create(['company_id' => $company->id]);
    $match = MatchRecord::factory()->create([
        'company_id' => $company->id,
        'press_release_id' => $release->id,
        'publication_item_id' => $item->id,
    ]);

    return OnePager::factory()->create([
        'match_id' => $match->id,
        'status' => $status,
    ]);
}
