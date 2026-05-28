<?php

use App\Livewire\Observatory\Edit;
use App\Models\Company;
use App\Models\Watch;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('creates a watch with normalised terms and the primary name auto-included', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['company' => $company])
        ->set('name', 'Newmont')
        ->set('kind', 'company')
        ->set('termsCsv', 'Newmont Mining, NEM,  ,  Newmont Goldcorp')
        ->set('mode', 'literal')
        ->call('save');

    $watch = Watch::firstWhere('company_id', $company->id);
    expect($watch)->not->toBeNull();
    expect($watch->terms)->toBe(['Newmont', 'Newmont Mining', 'NEM', 'Newmont Goldcorp']);
});

it('forces literal mode when team lacks the LLM upgrade, even if user selects literal_llm', function () {
    $user = makeUserWithTeam(['is_active' => true, 'llm_observatory_enabled' => false]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['company' => $company])
        ->set('name', 'Newmont')
        ->set('termsCsv', 'NEM')
        ->set('mode', 'literal_llm')
        ->call('save');

    $watch = Watch::firstWhere('company_id', $company->id);
    expect($watch->mode)->toBe('literal');
});

it('persists literal_llm mode when the team is entitled', function () {
    $user = makeUserWithTeam(['is_active' => true, 'llm_observatory_enabled' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['company' => $company])
        ->set('name', 'Newmont')
        ->set('termsCsv', 'NEM')
        ->set('mode', 'literal_llm')
        ->call('save');

    $watch = Watch::firstWhere('company_id', $company->id);
    expect($watch->mode)->toBe('literal_llm');
});

it('forbids editing a watch from another company', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $myCompany = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $otherCompany = Company::factory()->create(); // different team
    $foreign = Watch::create([
        'company_id' => $otherCompany->id,
        'name' => 'Foreign watch',
        'kind' => 'company',
        'terms' => ['Foreign'],
        'mode' => 'literal',
    ]);

    actingAs($user)
        ->get(route('companies.observatory.edit', ['company' => $myCompany, 'watch' => $foreign]))
        ->assertNotFound();
});
