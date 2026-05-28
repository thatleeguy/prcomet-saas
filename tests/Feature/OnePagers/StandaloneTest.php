<?php

use App\Livewire\OnePagers\Edit;
use App\Models\Company;
use App\Models\OnePager;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/**
 * Standalone one-pagers — pages that aren't tied to a match. Tests the
 * create endpoint, the dual-mode editor, and the cross-company scope
 * check on the UUID-keyed edit URL.
 */

it('creates a blank standalone one-pager from POST and redirects into the editor', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    actingAs($user);

    $response = post(route('companies.onepagers.store', $company));

    $page = OnePager::firstWhere('company_id', $company->id);
    expect($page)->not->toBeNull();
    expect($page->match_id)->toBeNull();
    expect($page->status)->toBe(OnePager::STATUS_DRAFT);
    expect($page->title)->toBeNull();
    expect($page->created_by_id)->toBe($user->id);

    $response->assertRedirect(route('companies.onepagers.edit', [
        'company' => $company,
        'onePager' => $page,
    ]));
});

it('forbids creating a one-pager on another team\'s company', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $foreign = Company::factory()->create(); // independent team

    actingAs($user);

    post(route('companies.onepagers.store', $foreign))
        ->assertForbidden();

    expect(OnePager::count())->toBe(0);
});

it('opens a standalone one-pager in the editor and saves title + note', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $page = OnePager::create([
        'company_id' => $company->id,
        'match_id' => null,
        'created_by_id' => $user->id,
        'status' => OnePager::STATUS_DRAFT,
    ]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['company' => $company, 'onePager' => $page])
        ->set('title', 'Aurelian — investor introduction')
        ->set('note', 'Quick overview of the Big Sky project.')
        ->call('save');

    $page->refresh();
    expect($page->title)->toBe('Aurelian — investor introduction');
    expect($page->note_md)->toContain('Big Sky project');
});

it('returns 404 when editing a one-pager from another company', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $myCompany = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $otherCompany = Company::factory()->create(); // independent team
    $foreignPage = OnePager::create([
        'company_id' => $otherCompany->id,
        'status' => OnePager::STATUS_DRAFT,
    ]);

    actingAs($user)
        ->get(route('companies.onepagers.edit', [
            'company' => $myCompany,
            'onePager' => $foreignPage,
        ]))
        ->assertNotFound();
});

it('renders displayTitle for a row without a title or a match', function () {
    $page = new OnePager(['title' => null, 'match_id' => null]);
    expect($page->displayTitle())->toBe('Untitled one-pager');
    expect($page->isStandalone())->toBeTrue();
});

it('publishes and unpublishes a standalone one-pager', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $page = OnePager::create([
        'company_id' => $company->id,
        'status' => OnePager::STATUS_DRAFT,
    ]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['company' => $company, 'onePager' => $page])
        ->set('title', 'Anything')
        ->call('publish');

    expect($page->fresh()->status)->toBe(OnePager::STATUS_PUBLISHED);
    expect($page->fresh()->published_at)->not->toBeNull();

    Livewire::actingAs($user)
        ->test(Edit::class, ['company' => $company, 'onePager' => $page->fresh()])
        ->call('unpublish');

    expect($page->fresh()->status)->toBe(OnePager::STATUS_UNPUBLISHED);
});
