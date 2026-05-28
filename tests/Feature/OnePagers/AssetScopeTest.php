<?php

use App\Livewire\OnePagers\Edit as OnePagerEdit;
use App\Models\Company;
use App\Models\MatchRecord;
use App\Models\MediaAsset;
use App\Models\PressRelease;
use App\Models\PublicationItem;
use App\Models\Source;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/**
 * The OnePagers/Edit screen lets the user toggle assets in/out of the
 * shareable page. selectedAssetIds is wire:click-controllable, so any
 * id can be POSTed by a tampered client — these tests verify both the
 * UI guard (toggleAsset) and the save-time filter reject foreign assets.
 */

it('refuses to add an asset from another company via toggleAsset', function () {
    [$user, $myCompany, $otherCompany] = setupTwoCompanies();
    $match = makeMatchInCompany($myCompany);
    $otherAsset = MediaAsset::factory()->create(['company_id' => $otherCompany->id]);

    actingAs($user);

    Livewire::test(OnePagerEdit::class, ['match' => $match])
        ->call('toggleAsset', $otherAsset->id)
        ->assertSet('selectedAssetIds', fn ($ids) => ! in_array($otherAsset->id, $ids, true));
});

it('drops foreign asset ids at save time even if state was tampered with', function () {
    [$user, $myCompany, $otherCompany] = setupTwoCompanies();
    $match = makeMatchInCompany($myCompany);
    $myAsset = MediaAsset::factory()->create(['company_id' => $myCompany->id]);
    $otherAsset = MediaAsset::factory()->create(['company_id' => $otherCompany->id]);

    actingAs($user);

    Livewire::test(OnePagerEdit::class, ['match' => $match])
        // Simulate a tampered Livewire payload that bypasses toggleAsset.
        ->set('selectedAssetIds', [$myAsset->id, $otherAsset->id])
        ->call('save')
        ->assertHasNoErrors();

    $attached = $match->ensureOnePager($user->id)
        ->assets()->pluck('media_assets.id')->all();

    expect($attached)->toContain($myAsset->id);
    expect($attached)->not->toContain($otherAsset->id);
});

it('allows assets from the matched company through', function () {
    [$user, $myCompany] = setupTwoCompanies();
    $match = makeMatchInCompany($myCompany);
    $asset = MediaAsset::factory()->create(['company_id' => $myCompany->id]);

    actingAs($user);

    // Start from an empty selection so toggle adds (not removes the curated default).
    Livewire::test(OnePagerEdit::class, ['match' => $match])
        ->set('selectedAssetIds', [])
        ->call('toggleAsset', $asset->id)
        ->assertSet('selectedAssetIds', fn ($ids) => in_array($asset->id, $ids, true));
});

/**
 * Build a user with two companies — one their team owns, one an outsider's.
 *
 * @return array{0: \App\Models\User, 1: \App\Models\Company, 2: \App\Models\Company}
 */
function setupTwoCompanies(): array
{
    $user = makeUserWithTeam(['is_active' => true]);
    $mine = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $theirs = Company::factory()->create(); // independent team

    return [$user, $mine, $theirs];
}

function makeMatchInCompany(Company $company): MatchRecord
{
    $source = Source::factory()->create();
    $item = PublicationItem::factory()->create(['source_id' => $source->id]);
    $release = PressRelease::factory()->create(['company_id' => $company->id]);

    return MatchRecord::factory()->create([
        'company_id' => $company->id,
        'press_release_id' => $release->id,
        'publication_item_id' => $item->id,
        'status' => MatchRecord::STATUS_NEW,
    ]);
}
