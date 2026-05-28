<?php

use App\Livewire\Companies\MediaAssetEdit;
use App\Models\Company;
use App\Models\MediaAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/**
 * The dedicated asset editor adds three capabilities to the old modal:
 *  - photo credit + credit URL
 *  - media-release toggle (blanket vs custom override)
 *  - revision history with one-click revert
 *
 * These tests cover those behaviours end-to-end.
 */

it('forbids editing an asset that belongs to another company', function () {
    Storage::fake('public');

    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $myCompany = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $other = Company::factory()->create(); // different team
    $foreignAsset = MediaAsset::factory()->image()->create(['company_id' => $other->id]);

    // Route binding hits the page with the foreign asset id under my company URL.
    actingAs($user)
        ->get(route('companies.library.edit', ['company' => $myCompany, 'asset' => $foreignAsset]))
        ->assertNotFound();
});

it('saves a credit and credit URL on a file asset', function () {
    Storage::fake('public');

    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    Livewire::actingAs($user)
        ->test(MediaAssetEdit::class, ['company' => $company])
        ->set('type', 'image')
        ->set('name', 'Drill core photo')
        ->set('file', UploadedFile::fake()->image('core.jpg', 1200, 800))
        ->set('credit', '© Mary Sutton / Aurelian')
        ->set('creditUrl', 'https://marysutton.photography')
        ->call('save');

    $asset = MediaAsset::firstWhere('company_id', $company->id);
    expect($asset->credit)->toBe('© Mary Sutton / Aurelian');
    expect($asset->credit_url)->toBe('https://marysutton.photography');
});

it('persists a per-asset media release override', function () {
    Storage::fake('public');

    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create([
        'team_id' => $user->currentTeam->id,
        'blanket_media_release_text' => 'BLANKET-RELEASE',
    ]);

    Livewire::actingAs($user)
        ->test(MediaAssetEdit::class, ['company' => $company])
        ->set('type', 'image')
        ->set('name', 'Third-party photo')
        ->set('file', UploadedFile::fake()->image('shot.jpg'))
        ->set('usesBlanketRelease', false)
        ->set('mediaReleaseText', 'Editorial use only, credit photographer, expires 30 Jun 2026.')
        ->call('save');

    $asset = MediaAsset::firstWhere('company_id', $company->id);
    expect($asset->uses_blanket_release)->toBeFalse();
    expect($asset->media_release_text)->toContain('Editorial use only');
    expect($asset->effectiveReleaseText())->toContain('Editorial use only')
        ->not->toContain('BLANKET-RELEASE');
});

it('inherits the blanket release when uses_blanket_release is true', function () {
    Storage::fake('public');

    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create([
        'team_id' => $user->currentTeam->id,
        'blanket_media_release_text' => 'BLANKET-TEXT',
    ]);

    $asset = MediaAsset::factory()->image()->create([
        'company_id' => $company->id,
        'uses_blanket_release' => true,
        'media_release_text' => null,
    ]);

    expect($asset->effectiveReleaseText())->toBe('BLANKET-TEXT');
});

it('snapshots the existing file as a revision when a replacement is uploaded', function () {
    Storage::fake('public');

    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    // Seed an asset with a real file on the fake disk.
    Storage::disk('public')->put('media/1/original.jpg', 'original-bytes');
    $asset = MediaAsset::factory()->image()->create([
        'company_id' => $company->id,
        'file_path' => 'media/1/original.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 14,
    ]);

    Livewire::actingAs($user)
        ->test(MediaAssetEdit::class, ['company' => $company, 'asset' => $asset])
        ->set('file', UploadedFile::fake()->image('better.jpg', 1600, 1200))
        ->set('revisionNotes', 'Final colour-corrected version')
        ->call('save');

    $asset->refresh();
    expect($asset->revisions)->toHaveCount(1);
    expect($asset->revisions->first()->file_path)->toBe('media/1/original.jpg');
    expect($asset->revisions->first()->notes)->toBe('Final colour-corrected version');
    expect($asset->file_path)->not->toBe('media/1/original.jpg'); // file got replaced
});

it('reverts to a prior revision when "Make current" is clicked', function () {
    Storage::fake('public');

    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    Storage::disk('public')->put('media/x/original.jpg', 'orig');
    $asset = MediaAsset::factory()->image()->create([
        'company_id' => $company->id,
        'file_path' => 'media/x/original.jpg',
    ]);

    Livewire::actingAs($user)
        ->test(MediaAssetEdit::class, ['company' => $company, 'asset' => $asset])
        ->set('file', UploadedFile::fake()->image('replacement.jpg'))
        ->call('save');

    $asset->refresh();
    $newPath = $asset->file_path;
    $revisionId = $asset->revisions->first()->id;

    // Revert.
    Livewire::actingAs($user)
        ->test(MediaAssetEdit::class, ['company' => $company, 'asset' => $asset->fresh()])
        ->call('revertTo', $revisionId);

    $asset->refresh();
    expect($asset->file_path)->toBe('media/x/original.jpg');

    // A revert snapshots the displaced "current" before swap, so there's
    // still a single row in history (the replacement we just displaced).
    expect($asset->revisions)->toHaveCount(1);
    expect($asset->revisions->first()->file_path)->toBe($newPath);
});

it('refuses to revert to a revision that belongs to another asset', function () {
    Storage::fake('public');

    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    $assetA = MediaAsset::factory()->image()->create(['company_id' => $company->id, 'file_path' => 'media/a.jpg']);
    $assetB = MediaAsset::factory()->image()->create(['company_id' => $company->id, 'file_path' => 'media/b.jpg']);

    $foreignRevision = $assetB->revisions()->create([
        'file_path' => 'media/b.older.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 100,
    ]);

    Livewire::actingAs($user)
        ->test(MediaAssetEdit::class, ['company' => $company, 'asset' => $assetA])
        ->call('revertTo', $foreignRevision->id);
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
