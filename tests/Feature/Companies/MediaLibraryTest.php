<?php

use App\Livewire\Companies\MediaAssetEdit;
use App\Livewire\Companies\MediaLibrary;
use App\Models\Company;
use App\Models\MediaAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('lists assets for the current team\'s company only', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $foreign = Company::factory()->create();

    MediaAsset::factory()->image()->create(['company_id' => $company->id, 'name' => 'My logo']);
    MediaAsset::factory()->image()->create(['company_id' => $foreign->id, 'name' => 'Foreign logo']);

    Livewire::actingAs($user)
        ->test(MediaLibrary::class, ['company' => $company])
        ->assertSee('My logo')
        ->assertDontSee('Foreign logo');
});

it('forbids accessing another team\'s media library', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $foreign = Company::factory()->create();

    actingAs($user)
        ->get(route('companies.library', $foreign))
        ->assertForbidden();
});

it('adds a pull-quote asset from the dedicated editor page', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    Livewire::actingAs($user)
        ->test(MediaAssetEdit::class, ['company' => $company])
        ->set('type', 'quote')
        ->set('name', 'Walker Lane quote')
        ->set('quoteText', 'Walker Lane is the most interesting setup of the quarter.')
        ->set('quoteAttribution', 'Sarah Chen, CEO')
        ->set('tagsCsv', 'Gold, Nevada')
        ->call('save');

    $asset = MediaAsset::firstWhere('company_id', $company->id);
    expect($asset)->not->toBeNull()
        ->and($asset->type)->toBe(MediaAsset::TYPE_QUOTE)
        ->and($asset->quote_text)->toContain('Walker Lane')
        ->and($asset->quote_attribution)->toBe('Sarah Chen, CEO')
        ->and($asset->tags)->toBe(['gold', 'nevada']);
});

it('uploads an image to the public disk', function () {
    Storage::fake('public');

    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    Livewire::actingAs($user)
        ->test(MediaAssetEdit::class, ['company' => $company])
        ->set('type', 'image')
        ->set('name', 'Big Sky core photo')
        ->set('file', UploadedFile::fake()->image('core.jpg', 1200, 800))
        ->call('save');

    $asset = MediaAsset::firstWhere('company_id', $company->id);
    expect($asset->type)->toBe(MediaAsset::TYPE_IMAGE);
    expect($asset->file_path)->not->toBeNull();
    Storage::disk('public')->assertExists($asset->file_path);
});

it('adds an external link asset', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    Livewire::actingAs($user)
        ->test(MediaAssetEdit::class, ['company' => $company])
        ->set('type', 'link')
        ->set('name', 'Analyst report')
        ->set('url', 'https://example.com/report.html')
        ->call('save');

    $asset = MediaAsset::firstWhere('company_id', $company->id);
    expect($asset->type)->toBe(MediaAsset::TYPE_LINK)
        ->and($asset->url)->toBe('https://example.com/report.html');
});

it('requires the quote text and attribution', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    Livewire::actingAs($user)
        ->test(MediaAssetEdit::class, ['company' => $company])
        ->set('type', 'quote')
        ->set('name', 'Anything')
        ->call('save')
        ->assertHasErrors(['quoteText' => 'required', 'quoteAttribution' => 'required']);
});

it('deletes an asset and its file', function () {
    Storage::fake('public');

    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    Storage::disk('public')->put('media/test.jpg', 'fake content');
    $asset = MediaAsset::factory()->image()->create([
        'company_id' => $company->id,
        'file_path' => 'media/test.jpg',
    ]);

    Livewire::actingAs($user)
        ->test(MediaLibrary::class, ['company' => $company])
        ->call('delete', $asset->id);

    expect(MediaAsset::find($asset->id))->toBeNull();
    Storage::disk('public')->assertMissing('media/test.jpg');
});
